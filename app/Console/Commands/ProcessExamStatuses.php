<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Services\ResultService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
                    $this->info("Exam '{$exam->title}' set to scheduled.");
                }
            } elseif ($today->between($start, $end)) {
                // Exam is in progress
                if ($exam->status !== 'in_progress') {
                    $exam->update(['status' => 'in_progress']);
                    $this->info("Exam '{$exam->title}' set to in_progress.");
                }
            } elseif ($today->gt($end)) {
                // After exam period: check if term result is finalized
                $termResultExists = TermResult::forExamType($exam->exam_type_id)
                    ->forAcademicYear($exam->academic_year_id)
                    ->whereHas('exam', function ($q) use ($exam) {
                        $q->where('class_id', $exam->class_id)
                            ->when($exam->section_id, fn($sq) => $sq->where('section_id', $exam->section_id));
                    })
                    ->exists();

                if ($termResultExists) {
                    if ($exam->status !== 'completed') {
                        $exam->update(['status' => 'completed']);
                        $this->info("Exam '{$exam->title}' marked as completed (term results finalized).");
                    }
                } else {
                    if ($exam->status !== 'result_entery') {
                        $exam->update(['status' => 'result_entery']);
                        // Attempt to calculate term result here
                        try {
                            DB::transaction(function () use ($exam) {
                                app(ResultService::class)->finalizeTermResult($exam);
                                $exam->update(['status' => 'completed']);
                                if ($exam->examType && $exam->examType->isFinalTerm()) {
                                    $this->finalizeAcademicResults();
                                }
                            });

                            $this->info("Exam '{$exam->title}' moved to result_entery and TermResult updated.");
                        } catch (\Throwable $e) {
                            $this->error("Failed to calculate TermResult for exam '{$exam->title}': " . $e->getMessage());
                        }
                    }
                }
            }
        }

        if ($today->gt($end)) {
            $termResultExists = TermResult::exam($exam)
                ->exists();

            if (!$termResultExists) {
                DB::transaction(function () use ($exam) {
                    app(ResultService::class)->finalizeTermResult($exam);
                    $exam->update(['status' => 'completed']);
                });

                $this->info("Finalized term result for exam '{$exam->title}'");
            } else {
                // Results exist, just mark as completed if not already
                if ($exam->status !== 'completed') {
                    $exam->update(['status' => 'completed']);
                    $this->info("Marked exam '{$exam->title}' as completed (term result already exists)");
                }
            }
        }


        $this->info("Exam processing completed.");
    }

    protected function finalizeAcademicResults()
    {
        $students = Student::admitted()->get();
        $academicYears = AcademicYear::all(); // or just active year if applicable

        foreach ($students as $student) {
            foreach ($academicYears as $year) {
                $termResults = TermResult::student($student)
                    ->academicYear($year)
                    // ->varified()
                    ->get();

                $termTypes = $termResults->pluck('exam_type_id')->unique();
                $expectedTermTypes = ExamType::pluck('id')->toArray();

                $hasFinalTerm = ExamType::isFinalTerm()
                    ->whereIn('id', $termTypes)
                    ->exists();

                // Proceed only if all expected terms and final term are included
                if (count($termTypes) === count($expectedTermTypes) && $hasFinalTerm) {
                    $overall = round($termResults->avg('overall_percentage'), 2);
                    $totalMarks = $termResults->sum('total_marks');
                    $obtainedMarks = $termResults->sum('obtained_marks');
                    $grade = $this->getGrade($overall);
                    $promotionStatus = $overall >= 40 ? 'promoted' : 'failed';

                    AcademicResult::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $year->id,
                            'class_id' => $student->class_id,
                            'section_id' => $student->section_id,
                        ],
                        [
                            'total_marks' => $totalMarks,
                            'obtained_marks' => $obtainedMarks,
                            'overall_percentage' => $overall,
                            'cumulative_gpa' => $this->calculateGPA($overall),
                            'final_grade' => $grade,
                            'promotion_status' => $promotionStatus,
                        ]
                    );

                    $this->info("Academic result finalized for {$student->name} ({$year->name})");
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
