<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Services\ResultService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\TermResult;
use Modules\ResultsPromotions\app\Models\AcademicResult;
use Modules\ResultsPromotions\app\Models\ExamType;

class ProcessExamStatuses extends Command
{
    protected $signature = 'exams:process-statuses';
    protected $description = 'Update exam statuses and trigger finalizations';

    public function handle()
    {
        ini_set('max_execution_time', 18000);
        $today = Carbon::today();

        // Handle exams by status
        $this->processScheduledExams($today);
        $this->processInProgressExams($today);
        $this->processResultEntryExams($today);

        $this->info("Exam processing completed.");
    }

    protected function processScheduledExams(Carbon $today)
    {
        $scheduledExams = Exam::where('status', '!=', 'cancelled')
            ->whereDate('start_date', '>', $today)
            ->whereNotIn('status', ['scheduled'])
            ->get();

        foreach ($scheduledExams as $exam) {
            $exam->update(['status' => 'scheduled']);
            Log::info("Exam '{$exam->title}' set to scheduled.");
            $this->info("Exam '{$exam->title}' set to scheduled.");
        }
    }

    protected function processInProgressExams(Carbon $today)
    {
        $inProgressExams = Exam::where('status', '!=', 'cancelled')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', ['in_progress'])
            ->get();

        foreach ($inProgressExams as $exam) {
            $exam->update(['status' => 'in_progress']);
            Log::info("Exam '{$exam->title}' set to in_progress.");
            $this->info("Exam '{$exam->title}' set to in_progress.");
        }
    }

    protected function processResultEntryExams(Carbon $today)
    {
        $expiredExams = Exam::with(['examType'])
            ->where('status', '!=', 'cancelled')
            ->whereDate('end_date', '<', $today)
            ->whereNotIn('status', ['completed'])
            ->get();

        foreach ($expiredExams as $exam) {
            $termResultExists = TermResult::forExamType($exam->exam_type_id)
                ->forExam($exam->id)
                ->academicYear($exam->academic_year_id)
                ->exists();

            if ($termResultExists) {
                $exam->update(['status' => 'completed']);
                Log::info("Exam '{$exam->title}' marked as completed (term results finalized).");
                $this->info("Exam '{$exam->title}' marked as completed.");
            } else {
                $exam->update(['status' => 'result_entery']);
                Log::info("Exam '{$exam->title}' marked as result_entery.");

                try {
                    DB::transaction(function () use ($exam) {
                        app(ResultService::class)->finalizeTermResult($exam);
                        $exam->update(['status' => 'completed']);

                        if ($exam->examType && $exam->examType->isFinalTerm()) {
                            $this->finalizeAcademicResults();
                        }
                    });

                    Log::info("Term results finalized for '{$exam->title}'");
                    $this->info("Term results finalized for '{$exam->title}'");
                } catch (\Throwable $e) {
                    Log::error("Failed to finalize term result for '{$exam->title}': " . $e->getMessage());
                    $this->error("Failed to finalize term result for '{$exam->title}': " . $e->getMessage());
                }
            }

            // Ensure academic result is created if needed
            if ($exam->examType && $exam->examType->isFinalTerm() && $termResultExists) {
                try {
                    $this->finalizeAcademicResults();
                } catch (\Throwable $e) {
                    Log::error("Academic result finalization failed for '{$exam->title}': " . $e->getMessage());
                    $this->error("Academic result finalization failed: " . $e->getMessage());
                }
            }
        }
    }

    protected function finalizeAcademicResults()
    {
        Log::info("Starting academic result finalization...");
        $academicYears = AcademicYear::all();
        $this->info("Academic results finalizing...");

        Student::admitted()->chunk(100, function ($students) use ($academicYears) {
            foreach ($students as $student) {
                foreach ($academicYears as $year) {
                    try {
                        DB::transaction(function () use ($student, $year) {
                            $termResults = TermResult::forStudent($student->id)
                                ->academicYear($year->id)
                                ->get();

                            $termTypes = $termResults->pluck('exam_type_id')->unique();
                            $expectedTermTypes = ExamType::pluck('id')->toArray();
                            $hasFinalTerm = ExamType::isFinalTerm()
                                ->whereIn('id', $termTypes)
                                ->exists();

                            if (count($termTypes) !== count($expectedTermTypes) || !$hasFinalTerm) {
                                throw new \Exception("Incomplete term data for {$student->name} ({$year->name})");
                            }

                            $overall = round($termResults->avg('overall_percentage'), 2);
                            $totalMarks = $termResults->sum('total_marks');
                            $obtainedMarks = $termResults->sum('obtained_marks');
                            $grade = $this->getGrade($overall);
                            $promotionStatus = $overall >= 40 ? 'promoted' : 'failed';

                            AcademicResult::updateOrCreate([
                                'student_id' => $student->id,
                                'academic_year_id' => $year->id,
                            ], [
                                'class_id' => $student->class_id,
                                'section_id' => $student->section_id,
                                'total_marks' => $totalMarks,
                                'obtained_marks' => $obtainedMarks,
                                'overall_percentage' => $overall,
                                'cumulative_gpa' => $this->calculateGPA($overall),
                                'final_grade' => $grade,
                                'promotion_status' => $promotionStatus,
                            ]);

                            Log::info("Academic result finalized for {$student->name} ({$year->name})");
                            $this->info("Academic result finalized for {$student->name} ({$year->name})");
                        });
                    } catch (\Exception $e) {
                        if (str_contains($e->getMessage(), 'Incomplete term data')) {
                            Log::warning($e->getMessage());
                            $this->warn($e->getMessage());
                        } else {
                            Log::error("Error finalizing result for {$student->name} ({$year->name}): " . $e->getMessage());
                            $this->error("Failed for {$student->name} ({$year->name}): " . $e->getMessage());
                        }
                    }
                }
            }
        });
    }

    /**
     * Convert percentage to GPA
     */
    protected function calculateGPA(float $percentage): float
    {
        if ($percentage < 50) {
            return 0.0;
        }
        $gpa = 2.0 + (($percentage - 50) / 40) * 2.0;
        return round(min($gpa, 4.0), 2);
    }
    private function getGrade($percentage)
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }
}
