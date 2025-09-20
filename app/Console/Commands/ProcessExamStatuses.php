<?php

namespace App\Console\Commands;

use App\Services\ResultService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\TermResult;
use Modules\ResultsPromotions\app\Models\AcademicResult;

class ProcessExamStatuses extends Command
{
    protected $signature = 'exams:process-statuses';
    protected $description = 'Update exam statuses and trigger finalizations';

    public function handle()
    {
        $today = Carbon::today();

        // 1. Update Exam Status
        $exams = Exam::whereIn('status', ['scheduled', 'in_progress'])->get();
        foreach ($exams as $exam) {
            $start = Carbon::parse($exam->start_date);
            $end = Carbon::parse($exam->end_date);

            if ($today->lt($start)) continue;
            elseif ($today->between($start, $end)) {
                if ($exam->status !== 'in_progress') {
                    $exam->update(['status' => 'in_progress']);
                    $this->info("Exam '{$exam->title}' marked as in_progress.");
                }
            }
        }

        // 2. Finalize Exam & Term Result if all marks entered
        $exams = Exam::with(['examPapers.results', 'class', 'section', 'examType'])
            ->where('status', 'in_progress')
            ->get();

        foreach ($exams as $exam) {
            $studentsCount = Student::where('class_id', $exam->class_id)
                ->when($exam->section_id, fn($q) => $q->where('section_id', $exam->section_id))
                ->admitted()
                ->count();

            $allResultsEntered = true;
            foreach ($exam->examPapers as $paper) {
                if ($paper->results->count() < $studentsCount) {
                    $allResultsEntered = false;
                    break;
                }
            }

            if ($allResultsEntered) {
                DB::transaction(function () use ($exam) {
                    $exam->update(['status' => 'completed']);
                    app(ResultService::class)->finalizeTermResult($exam);
                });

                $this->info("Finalized exam and term result: {$exam->title}");
            }
        }

        // 3. Check and Finalize Academic Results (if all term results available)
        $this->finalizeAcademicResults();

        $this->info("Exam processing completed.");
    }

    protected function finalizeAcademicResults()
    {
        $students = Student::admitted()->get();

        foreach ($students as $student) {
            $termResults = TermResult::where('student_id', $student->id)
                ->whereNotNull('overall_percentage') // Ensure it's finalized
                ->orderBy('exam_type_id') // Adjust ordering based on your term system
                ->get();

            if ($termResults->count() < 3) continue; // Skip if not all terms calculated

            $academicYear = $termResults->first()->academic_year;

            $percentages = $termResults->pluck('overall_percentage');
            $term1 = $percentages[0];
            $term2 = $percentages[1];
            $term3 = $percentages[2];

            $overall = round(($term1 + $term2 + $term3) / 3, 2);
            $grade = $this->getGrade($overall);
            $promotionStatus = $overall >= 40 ? 'promoted' : 'detained';

            AcademicResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $academicYear,
                    'class_id' => $student->class_id,
                    'section_id' => $student->section_id,
                ],
                [
                    'term1_percentage' => $term1,
                    'term2_percentage' => $term2,
                    'term3_percentage' => $term3,
                    'overall_percentage' => $overall,
                    'final_grade' => $grade,
                    'promotion_status' => $promotionStatus,
                ]
            );

            $this->info("Academic result finalized for student: {$student->name}");
        }
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
