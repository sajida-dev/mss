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
        // Step 1: Get all students for this class + section
        $students = Student::where('class_id', $exam->class_id)
            ->when($exam->section_id, fn($q) => $q->where('section_id', $exam->section_id))
            ->admitted()
            ->get();

        foreach ($students as $student) {
            $examPapers = $exam->examPapers;

            $results = ExamResult::whereIn('exam_paper_id', $examPapers->pluck('id'))
                ->where('student_id', $student->id)
                ->get();

            $totalObtained = $results->sum('obtained_marks');
            $totalMax = $results->sum('total_marks');

            $subjectsPassed = $results->where('status', 'pass')->count();
            $subjectsFailed = $results->where('status', 'fail')->count();

            $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

            $termStatus = $subjectsFailed === 0 ? 'pass' : 'fail';

            // Save or update term result
            TermResult::updateOrCreate([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
            ], [
                'exam_type_id' => $exam->exam_type_id,
                'academic_year' => $exam->academic_year,
                'total_subjects' => $examPapers->count(),
                'total_marks_obtained' => $totalObtained,
                'total_maximum_marks' => $totalMax,
                'overall_percentage' => $percentage,
                'subjects_passed' => $subjectsPassed,
                'subjects_failed' => $subjectsFailed,
                'term_status' => $termStatus,
                'grade' => $this->getGrade($percentage),
                'grade_points' => $this->calculateGPA($percentage),
            ]);
        }
    }

    /**
     * Convert percentage to GPA
     */
    protected function calculateGPA($percentage): float
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 80) return 3.7;
        if ($percentage >= 70) return 3.0;
        if ($percentage >= 60) return 2.0;
        if ($percentage >= 50) return 1.0;
        return 0.0;
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
