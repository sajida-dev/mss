<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamResult;
use Modules\ResultsPromotions\app\Models\TermResult;

class ResultService
{
    /**
     * Finalize term result for a given exam (per class).
     */
    public function finalizeTermResult(Exam $exam)
    {
        $students = Student::class($exam->class_id)
            ->admitted()
            ->get();

        foreach ($students as $student) {
            // Filter results for this exam
            $results = $student->results->where('examPaper.exam_id', $exam->id);

            if ($results->isEmpty()) continue;

            $obtained = $results->sum('obtained_marks');
            $total = $results->sum('total_marks');
            $percentage = round(($obtained / $total) * 100, 2);
            $grade = $this->getGrade($percentage);
            $passed = $results->where('status', 'pass')->count();
            $failed = $results->where('status', 'fail')->count();
            $subjectCount = $results->count();
            $gradePoints = $this->calculateGPA($percentage);

            TermResult::updateOrCreate([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'exam_type_id' => $exam->exam_type_id,
                'academic_year_id' => $exam->academic_year_id,

            ], [
                'total_subjects' => $subjectCount,
                'obtained_marks' => $obtained,
                'total_marks' => $total,
                'overall_percentage' => $percentage,
                'subjects_passed' => $passed,
                'subjects_failed' => $failed,
                'grade_points' => $gradePoints,
                'grade' => $grade,
                'term_status' => $percentage >= 40 ? 'pass' : 'fail',
            ]);
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

    /**
     * Convert percentage to grade
     */
    protected function getGrade($percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        return 'F';
    }
}
