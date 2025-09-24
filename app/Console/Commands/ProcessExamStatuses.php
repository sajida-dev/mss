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
        $today = Carbon::today();

        $exams = Exam::with(['examType', 'class', 'section'])
            ->whereNotIn('status', ['cancelled', 'completed']) // Skip already completed or cancelled
            ->get();



        foreach ($exams as $exam) {

            $start = Carbon::parse($exam->start_date);
            $end = Carbon::parse($exam->end_date);

            if ($today->lt($start)) {
                // Exam is still scheduled
                if ($exam->status !== 'scheduled') {
                    $exam->update(['status' => 'scheduled']);
                    Log::info("Exam '{$exam->title}' set to scheduled.");
                    $this->info("Exam '{$exam->title}' set to scheduled.");
                }
            } elseif ($today->between($start, $end)) {
                // Exam is in progress
                if ($exam->status !== 'in_progress') {
                    $exam->update(['status' => 'in_progress']);
                    Log::info("Exam '{$exam->title}' set to in_progress.");
                    $this->info("Exam '{$exam->title}' set to in_progress.");
                }
            } elseif ($today->gt($end)) {
                // After exam period: check if term result is finalized
                $termResultExists = TermResult::forExamType($exam->exam_type_id)
                    ->forExam($exam->id)
                    ->forAcademicYear($exam->academic_year_id)
                    ->exists();

                if ($termResultExists) {
                    if ($exam->status !== 'completed') {
                        $exam->update(['status' => 'completed']);
                        Log::info("Exam '{$exam->title}' marked as completed (term results finalized).");
                        $this->info("Exam '{$exam->title}' marked as completed (term results finalized).");
                    }
                } else {
                    if ($exam->status !== 'result_entery') {
                        $exam->update(['status' => 'result_entery']);
                        Log::info("Exam '{$exam->title}' marked as result_entery.");
                    }
                    // Attempt to calculate term result here
                    try {
                        DB::transaction(function () use ($exam) {
                            app(ResultService::class)->finalizeTermResult($exam);
                            $exam->update(['status' => 'completed']);
                            if ($exam->examType && $exam->examType->isFinalTerm()) {
                                $this->finalizeAcademicResults();
                            }
                        });
                        Log::info("Exam '{$exam->title}' moved to result_entery and TermResult updated.");
                        $this->info("Exam '{$exam->title}' moved to result_entery and TermResult updated.");
                    } catch (\Throwable $e) {
                        Log::error("Failed to calculate TermResult for exam '{$exam->title}': " . $e->getMessage());
                        $this->error("Failed to calculate TermResult for exam '{$exam->title}': " . $e->getMessage());
                    }
                }
            }

            try {
                DB::transaction(function () use ($exam) {
                    $termResultExists = TermResult::forExamType($exam->exam_type_id)
                        ->forExam($exam->id)
                        ->forAcademicYear($exam->academic_year_id)
                        ->exists();

                    if ($exam->examType && $exam->examType->isFinalTerm() && $termResultExists) {
                        Log::info("Exam '{$exam->title}' Academic results finalizing...");
                        $this->finalizeAcademicResults();
                    }
                    Log::info("Exam '{$exam->title}' Academic results finalized.");
                    $this->info("Exam '{$exam->title}' Academic results finalized.");
                });
            } catch (\Throwable $e) {
                Log::error("Failed to calculate Academic results for academic year {$exam->academic_year_name}: " . $e->getMessage());
                $this->error("Failed to calculate Academic results for academic year {$exam->academic_year_name}: " . $e->getMessage());
            }
        }

        $this->info("Exam processing completed.");
    }

    protected function finalizeAcademicResults()
    {
        $students = Student::admitted()->get();
        $academicYears = AcademicYear::all(); // Or filter active years if needed
        $this->info("Academic results finalizing...");
        Log::info("Academic results finalizing...");
        foreach ($students as $student) {
            $this->info("Processing student {$student->name}...");
            foreach ($academicYears as $year) {
                Log::info("Processing academic year {$year->name}...");
                $this->info("Processing academic year {$year->name}...");
                try {
                    DB::transaction(function () use ($student, $year) {
                        $termResults = TermResult::forStudent($student->id)
                            ->academicYear($year->id)
                            // ->verified() // Uncomment if verification is needed
                            ->get();
                        Log::info("Processing term results for student {$student->name} and academic year {$year->name}...");
                        $this->info("Processing term results for student {$student->name} and academic year {$year->name}...");

                        $termTypes = $termResults->pluck('exam_type_id')->unique();
                        $expectedTermTypes = ExamType::pluck('id')->toArray();

                        $hasFinalTerm = ExamType::isFinalTerm()
                            ->whereIn('id', $termTypes)
                            ->exists();
                        Log::info("Processing term results for student {$student->name} and academic year {$year->name}: {$hasFinalTerm}");
                        $this->info("Processing term results for student {$student->name} and academic year {$year->name}: {$hasFinalTerm}");

                        if (count($termTypes) !== count($expectedTermTypes) || !$hasFinalTerm) {
                            // Throw exception to rollback and skip processing for this student-year
                            throw new \Exception("Incomplete term data for {$student->name} ({$year->name})");
                        }

                        Log::info("Processing term results for student {$student->name} and academic year {$year->name}: Calculating GPA...");
                        $this->info("Processing term results for student {$student->name} and academic year {$year->name}: Calculating GPA...");
                        $overall = round($termResults->avg('overall_percentage'), 2);
                        $totalMarks = $termResults->sum('total_marks');
                        $obtainedMarks = $termResults->sum('obtained_marks');
                        $grade = $this->getGrade($overall);
                        $promotionStatus = $overall >= 40 ? 'promoted' : 'failed';
                        Log::info("Processing term results for student {$student->name} and academic year {$year->name}: GPA calculated.");
                        $this->info("Processing term results for student {$student->name} and academic year {$year->name}: GPA calculated.");
                        AcademicResult::create([
                            'student_id' => $student->id,
                            'academic_year_id' => $year->id,
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
                    // Handle incomplete data differently from other errors
                    if (str_contains($e->getMessage(), 'Incomplete term data')) {
                        Log::warning($e->getMessage());
                        $this->warn($e->getMessage());
                    } else {
                        Log::error("Error finalizing result for {$student->name} ({$year->name}): " . $e->getMessage());
                        $this->error("Failed for {$student->name} ({$year->name}): " . $e->getMessage());
                    }
                    // Continue processing other students/years regardless
                    continue;
                }
            }
        }
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
