<?php

namespace Modules\ResultsPromotions\app\Services;

use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamResult;
use Modules\ResultsPromotions\app\Models\TermResult;
use Modules\ResultsPromotions\app\Models\AcademicResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ResultCalculationService
{
    /**
     * Calculate and store term results for a student
     */
    public function calculateTermResult($studentId, $examId, $verifiedBy = null)
    {
        $exam = Exam::findOrFail($examId);
        $examResults = ExamResult::where('student_id', $studentId)
            ->whereHas('examPaper', function ($query) use ($examId) {
                $query->where('exam_id', $examId);
            })->get();

        if ($examResults->isEmpty()) {
            throw new \Exception('No exam results found for this student and exam');
        }

        // Calculate totals
        $totalSubjects = $examResults->count();
        $totalMarksObtained = $examResults->sum('marks_obtained');
        $totalMaximumMarks = $examResults->sum(function ($result) {
            return $result->examPaper->total_marks;
        });
        $overallPercentage = ($totalMaximumMarks > 0) ?
            round(($totalMarksObtained / $totalMaximumMarks) * 100, 2) : 0;

        $subjectsPassed = $examResults->where('status', 'pass')->count();
        $subjectsFailed = $examResults->where('status', 'fail')->count();

        // Determine term status
        $termStatus = $this->determineTermStatus($overallPercentage, $subjectsFailed, $totalSubjects);

        // Calculate GPA and Grade
        $gradePoints = $this->calculateGradePoints($overallPercentage);
        $grade = $this->calculateGrade($overallPercentage);

        // Store or update term result
        $termResult = TermResult::updateOrCreate(
            [
                'student_id' => $studentId,
                'exam_id' => $examId
            ],
            [
                'exam_type' => $exam->exam_type,
                'academic_year' => $exam->academic_year,
                'total_subjects' => $totalSubjects,
                'total_marks_obtained' => $totalMarksObtained,
                'total_maximum_marks' => $totalMaximumMarks,
                'overall_percentage' => $overallPercentage,
                'subjects_passed' => $subjectsPassed,
                'subjects_failed' => $subjectsFailed,
                'term_status' => $termStatus,
                'grade_points' => $gradePoints,
                'grade' => $grade,
                'is_verified' => $verifiedBy ? true : false,
                'verified_by' => $verifiedBy,
                'verified_at' => $verifiedBy ? now() : null,
            ]
        );

        return $termResult;
    }

    /**
     * Calculate final academic year result and promotion decision
     */
    public function calculateAcademicResult($studentId, $academicYear, $classId, $verifiedBy = null)
    {
        // Get all term results for the student in this academic year
        $termResults = TermResult::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->get();

        if ($termResults->isEmpty()) {
            throw new \Exception('No term results found for this student and academic year');
        }

        // Extract term-wise percentages
        $term1Percentage = $termResults->where('exam_type', '1st Term')->first()?->overall_percentage;
        $term2Percentage = $termResults->where('exam_type', '2nd Term')->first()?->overall_percentage;
        $term3Percentage = $termResults->where('exam_type', '3rd Term')->first()?->overall_percentage;
        $finalTermPercentage = $termResults->where('exam_type', 'Final Term')->first()?->overall_percentage;

        // Calculate overall percentage (weighted average)
        $overallPercentage = $this->calculateOverallPercentage([
            'term1' => $term1Percentage,
            'term2' => $term2Percentage,
            'term3' => $term3Percentage,
            'final' => $finalTermPercentage
        ]);

        // Calculate cumulative GPA
        $cumulativeGpa = $this->calculateCumulativeGPA($termResults);

        // Determine final grade
        $finalGrade = $this->calculateGrade($overallPercentage);

        // Determine promotion status
        $promotionStatus = $this->determinePromotionStatus($overallPercentage, $termResults);

        // Store or update academic result
        $academicResult = AcademicResult::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year' => $academicYear
            ],
            [
                'class_id' => $classId,
                'term1_percentage' => $term1Percentage,
                'term2_percentage' => $term2Percentage,
                'term3_percentage' => $term3Percentage,
                'final_term_percentage' => $finalTermPercentage,
                'overall_percentage' => $overallPercentage,
                'cumulative_gpa' => $cumulativeGpa,
                'final_grade' => $finalGrade,
                'promotion_status' => $promotionStatus,
                'is_verified' => $verifiedBy ? true : false,
                'verified_by' => $verifiedBy,
                'verified_at' => $verifiedBy ? now() : null,
            ]
        );

        return $academicResult;
    }

    /**
     * Verify term results (admin/teacher verification)
     */
    public function verifyTermResult($termResultId, $verifiedBy = null)
    {
        $termResult = TermResult::findOrFail($termResultId);

        $termResult->update([
            'is_verified' => true,
            'verified_by' => $verifiedBy ?? Auth::id(),
            'verified_at' => now(),
        ]);

        return $termResult;
    }

    /**
     * Approve final academic result (final admin approval)
     */
    public function approveAcademicResult($academicResultId, $approvedBy = null)
    {
        $academicResult = AcademicResult::findOrFail($academicResultId);

        $academicResult->update([
            'approved_by' => $approvedBy ?? Auth::id(),
            'approved_at' => now(),
        ]);

        return $academicResult;
    }

    /**
     * Determine term status based on percentage and failed subjects
     */
    private function determineTermStatus($percentage, $failedSubjects, $totalSubjects)
    {
        if ($percentage >= 40 && $failedSubjects == 0) {
            return 'pass';
        } elseif ($percentage >= 40 && $failedSubjects <= 1) {
            return 'pass'; // Allow 1 subject failure
        } else {
            return 'fail';
        }
    }

    /**
     * Calculate grade points (GPA)
     */
    private function calculateGradePoints($percentage)
    {
        if ($percentage >= 90) return 4.0;
        elseif ($percentage >= 85) return 3.7;
        elseif ($percentage >= 80) return 3.3;
        elseif ($percentage >= 75) return 3.0;
        elseif ($percentage >= 70) return 2.7;
        elseif ($percentage >= 65) return 2.3;
        elseif ($percentage >= 60) return 2.0;
        elseif ($percentage >= 55) return 1.7;
        elseif ($percentage >= 50) return 1.3;
        elseif ($percentage >= 40) return 1.0;
        else return 0.0;
    }

    /**
     * Calculate letter grade
     */
    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 'A+';
        elseif ($percentage >= 85) return 'A';
        elseif ($percentage >= 80) return 'B+';
        elseif ($percentage >= 75) return 'B';
        elseif ($percentage >= 70) return 'C+';
        elseif ($percentage >= 65) return 'C';
        elseif ($percentage >= 60) return 'D+';
        elseif ($percentage >= 55) return 'D';
        elseif ($percentage >= 40) return 'E';
        else return 'F';
    }

    /**
     * Calculate overall percentage (weighted average)
     */
    private function calculateOverallPercentage($termPercentages)
    {
        $weights = [
            'term1' => 0.15,  // 15%
            'term2' => 0.15,  // 15%
            'term3' => 0.15,  // 15%
            'final' => 0.55   // 55%
        ];

        $totalWeighted = 0;
        $totalWeight = 0;

        foreach ($weights as $term => $weight) {
            if ($termPercentages[$term] !== null) {
                $totalWeighted += $termPercentages[$term] * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? round($totalWeighted / $totalWeight, 2) : 0;
    }

    /**
     * Calculate cumulative GPA
     */
    private function calculateCumulativeGPA($termResults)
    {
        $totalGradePoints = $termResults->sum('grade_points');
        $totalTerms = $termResults->count();

        return $totalTerms > 0 ? round($totalGradePoints / $totalTerms, 2) : 0;
    }

    /**
     * Determine promotion status
     */
    private function determinePromotionStatus($overallPercentage, $termResults)
    {
        // Check if all terms are passed
        $allTermsPassed = $termResults->every(function ($term) {
            return $term->term_status === 'pass';
        });

        if ($overallPercentage >= 40 && $allTermsPassed) {
            return 'promoted';
        } elseif ($overallPercentage >= 35) {
            return 'conditional_promotion';
        } else {
            return 'repeat_class';
        }
    }
}
