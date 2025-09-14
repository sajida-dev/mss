<?php

namespace Modules\ResultsPromotions\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\ResultsPromotions\app\Services\ResultCalculationService;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\TermResult;
use Modules\ResultsPromotions\app\Models\AcademicResult;
use Modules\Admissions\app\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;

class ResultCalculationController extends Controller
{
    protected $resultService;

    public function __construct(ResultCalculationService $resultService)
    {
        $this->resultService = $resultService;
    }

    /**
     * Show term results calculation page
     */
    public function calculateTermResults($examId)
    {
        $exam = Exam::with(['papers', 'class', 'section'])->findOrFail($examId);

        // Get students for this class/section
        $students = Student::where('class', $exam->class->name)
            ->when($exam->section_id, function ($query) use ($exam) {
                $query->where('section', $exam->section->name);
            })
            ->get();

        return Inertia::render('Results/CalculateTermResults', [
            'exam' => $exam,
            'students' => $students
        ]);
    }

    /**
     * Calculate term results for all students
     */
    public function processTermResults(Request $request, $examId)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $results = [];
        $errors = [];

        foreach ($request->student_ids as $studentId) {
            try {
                $userId = $request->user()?->id ?? null;
                $termResult = $this->resultService->calculateTermResult($studentId, $examId, $userId);
                $results[] = $termResult;
            } catch (\Exception $e) {
                $errors[] = "Student ID {$studentId}: " . $e->getMessage();
            }
        }

        $message = count($results) > 0 ?
            count($results) . " term results calculated successfully." :
            "No results calculated.";

        if (count($errors) > 0) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Show term results for verification
     */
    public function verifyTermResults($examId)
    {
        $exam = Exam::with(['class', 'section'])->findOrFail($examId);
        $termResults = TermResult::with(['student', 'verifiedBy'])
            ->where('exam_id', $examId)
            ->orderBy('overall_percentage', 'desc')
            ->get();

        return Inertia::render('Results/VerifyTermResults', [
            'exam' => $exam,
            'termResults' => $termResults
        ]);
    }

    /**
     * Verify a term result
     */
    public function verifyTermResult(Request $request, $termResultId)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:500'
        ]);

        try {
            $userId = $request->user()?->id ?? null;
            $termResult = $this->resultService->verifyTermResult($termResultId, $userId);

            if ($request->remarks) {
                $termResult->update(['remarks' => $request->remarks]);
            }

            return back()->with('success', 'Term result verified successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to verify term result: ' . $e->getMessage());
        }
    }

    /**
     * Show academic year results calculation page
     */
    public function calculateAcademicResults()
    {
        $academicYears = TermResult::distinct()->pluck('academic_year');
        $classes = ClassModel::all();

        return Inertia::render('Results/CalculateAcademicResults', [
            'academicYears' => $academicYears,
            'classes' => $classes
        ]);
    }

    /**
     * Process academic year results
     */
    public function processAcademicResults(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'class_id' => 'required|exists:classes,id'
        ]);

        $students = Student::where('class', ClassModel::find($request->class_id)->name)->get();

        $results = [];
        $errors = [];

        foreach ($students as $student) {
            try {
                $userId = $request->user()?->id ?? null;
                $academicResult = $this->resultService->calculateAcademicResult(
                    $student->id,
                    $request->academic_year,
                    $request->class_id,
                    $userId
                );
                $results[] = $academicResult;
            } catch (\Exception $e) {
                $errors[] = "Student {$student->name}: " . $e->getMessage();
            }
        }

        $message = count($results) > 0 ?
            count($results) . " academic results calculated successfully." :
            "No results calculated.";

        if (count($errors) > 0) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Show academic results for approval
     */
    public function approveAcademicResults($academicYear, $classId)
    {
        $academicResults = AcademicResult::with(['student', 'class', 'section', 'verifiedBy', 'approvedBy'])
            ->where('academic_year', $academicYear)
            ->where('class_id', $classId)
            ->orderBy('overall_percentage', 'desc')
            ->get();

        return Inertia::render('Results/ApproveAcademicResults', [
            'academicYear' => $academicYear,
            'classId' => $classId,
            'academicResults' => $academicResults
        ]);
    }

    /**
     * Approve academic result
     */
    public function approveAcademicResult(Request $request, $academicResultId)
    {
        $request->validate([
            'promotion_remarks' => 'nullable|string|max:500'
        ]);

        try {
            $userId = $request->user()?->id ?? null;
            $academicResult = $this->resultService->approveAcademicResult($academicResultId, $userId);

            if ($request->promotion_remarks) {
                $academicResult->update(['promotion_remarks' => $request->promotion_remarks]);
            }

            return back()->with('success', 'Academic result approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve academic result: ' . $e->getMessage());
        }
    }

    /**
     * Show student result report
     */
    public function studentReport($studentId, $academicYear = null)
    {
        $student = Student::findOrFail($studentId);

        if (!$academicYear) {
            $academicYear = TermResult::where('student_id', $studentId)
                ->latest('academic_year')
                ->first()?->academic_year;
        }

        $termResults = TermResult::with(['exam'])
            ->where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->get();

        $academicResult = AcademicResult::with(['class', 'section'])
            ->where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->first();

        return Inertia::render('Results/StudentReport', [
            'student' => $student,
            'academicYear' => $academicYear,
            'termResults' => $termResults,
            'academicResult' => $academicResult
        ]);
    }
}
