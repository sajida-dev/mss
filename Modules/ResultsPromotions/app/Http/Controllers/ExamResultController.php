<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamResult;
use Modules\ResultsPromotions\app\Models\ExamType;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;

class ExamResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // public function index(Request $request)
    // {
    //     $schoolId = session('active_school_id');

    //     // Filters
    //     $selectedClass = $request->input('class_id');
    //     $selectedSection = $request->input('section_id');
    //     $selectedTerm = $request->input('term');
    //     $selectedAcademicYear = $request->input('academic_year_id');
    //     $selectedExamId = $request->input('exam_id'); // RENAMED for clarity

    //     // Classes
    //     $classes = ClassModel::whereHas('schools', fn($q) => $q->where('schools.id', $schoolId))
    //         ->orderBy('name')
    //         ->get(['id', 'name']);

    //     // Sections
    //     $sections = Section::whereIn('id', function ($query) use ($schoolId) {
    //         $query->select('class_school_sections.section_id')
    //             ->from('class_school_sections')
    //             ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
    //             ->where('class_schools.school_id', $schoolId);
    //     })->orderBy('name')->get(['id', 'name']);

    //     // Academic Years
    //     $academicYears = Exam::query()
    //         ->where('school_id', $schoolId)
    //         ->when($selectedTerm, function ($q) use ($selectedTerm) {
    //             $q->whereHas('examType', fn($q2) => $q2->where('code', $selectedTerm));
    //         })
    //         ->select('academic_year')
    //         ->distinct()
    //         ->orderByDesc('academic_year')
    //         ->pluck('academic_year')
    //         ->map(fn($year) => ['id' => $year, 'year' => $year])
    //         ->values();


    //     // Exams List Filtered
    //     $exams = Exam::where('school_id', $schoolId)
    //         ->when($selectedClass, fn($q) => $q->where('class_id', $selectedClass))
    //         ->when($selectedSection, fn($q) => $q->where('section_id', $selectedSection))
    //         ->when($selectedAcademicYear, fn($q) => $q->where('academic_year', $selectedAcademicYear))
    //         ->when($selectedTerm, fn($q) => $q->whereHas('examType', fn($q2) => $q2->where('code', $selectedTerm)))
    //         ->with('examType')
    //         ->orderBy('start_date', 'desc')
    //         ->get()
    //         ->map(fn($exam) => [
    //             'id' => $exam->id,
    //             'title' => $exam->title,
    //             'exam_type' => $exam->examType->name ?? '',
    //         ]);

    //     $results = collect();

    //     if ($selectedClass) {
    //         $students = Student::whereHas('class', fn($q) => $q->where('classes.id', $selectedClass))
    //             ->when($selectedSection, fn($q) => $q->whereHas('section', fn($sq) => $sq->where('sections.id', $selectedSection)))
    //             ->where('school_id', $schoolId)
    //             ->admitted()
    //             ->with([
    //                 'class',
    //                 'section',
    //                 'results.examPaper.exam.examType',
    //                 'results.examPaper.subject',
    //                 'results.markedBy'
    //             ])
    //             ->orderBy('registration_number')
    //             ->get();

    //         foreach ($students as $student) {
    //             $termResults = $student->results->filter(function ($result) use ($selectedExamId, $selectedTerm, $selectedAcademicYear) {
    //                 $exam = optional($result->examPaper->exam);
    //                 $examType = optional($exam->examType);

    //                 if ($selectedExamId) {
    //                     return $exam->id == $selectedExamId;
    //                 }

    //                 return $examType->code === $selectedTerm && $exam->academic_year == $selectedAcademicYear;
    //             });

    //             $resultItems = $termResults->map(function ($result) {
    //                 return [
    //                     'subject_id'      => $result->examPaper->subject_id,
    //                     'subject_name'    => optional($result->examPaper->subject)->name,
    //                     'obtained_marks'  => $result->obtained_marks,
    //                     'total_marks'     => $result->total_marks,
    //                     'percentage'      => $result->percentage,
    //                     'status'          => $result->status,
    //                     'promotion_status' => $result->promotion_status,
    //                     'remarks'         => $result->remarks,
    //                     'marked_by'       => optional($result->markedBy)->name ?? 'N/A',
    //                 ];
    //             });

    //             $obtainedTotal = $termResults->sum('obtained_marks');
    //             $possibleTotal = $termResults->sum('total_marks');

    //             $percentage = $possibleTotal > 0
    //                 ? round(($obtainedTotal / $possibleTotal) * 100, 2)
    //                 : 0;

    //             $results->push([
    //                 'student' => $student,
    //                 'results' => $resultItems,
    //                 'total_obtained_marks' => $obtainedTotal,
    //                 'total_possible_marks' => $possibleTotal,
    //                 'percentage' => $percentage,
    //                 'term_has_results' => $termResults->isNotEmpty(),
    //             ]);
    //         }
    //     }

    //     $terms = ExamType::pluck('name', 'code');

    //     return Inertia::render('ExamResults/Index', [
    //         'classes' => $classes,
    //         'sections' => $sections,
    //         'results' => $results,
    //         'academicYears' => $academicYears,
    //         'exams' => $exams,
    //         'terms' => $terms,

    //         // Selected filters
    //         'selectedClass' => $selectedClass,
    //         'selectedSection' => $selectedSection,
    //         'selectedTerm' => $selectedTerm,
    //         'selectedAcademicYear' => $selectedAcademicYear,
    //         'selectedExam' => $selectedExamId,
    //     ]);
    // }

    public function index(Request $request)
    {
        $schoolId = session('active_school_id');

        // Filters
        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedTerm = $request->input('term');
        $selectedAcademicYear = $request->input('academic_year_id');
        $selectedExamId = $request->input('exam_id');

        // Classes
        $classes = ClassModel::whereHas('schools', fn($q) => $q->where('schools.id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Sections
        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Academic Years
        $academicYears = Exam::query()
            ->where('school_id', $schoolId)
            ->when($selectedTerm, function ($q) use ($selectedTerm) {
                $q->whereHas('examType', fn($q2) => $q2->where('code', $selectedTerm));
            })
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year')
            ->map(fn($year) => ['id' => $year, 'year' => $year])
            ->values();

        // Exams List Filtered
        $exams = Exam::where('school_id', $schoolId)
            ->when($selectedClass, fn($q) => $q->where('class_id', $selectedClass))
            ->when($selectedSection, fn($q) => $q->where('section_id', $selectedSection))
            ->when($selectedAcademicYear, fn($q) => $q->where('academic_year', $selectedAcademicYear))
            ->when($selectedTerm, fn($q) => $q->whereHas('examType', fn($q2) => $q2->where('code', $selectedTerm)))
            ->with('examType')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'exam_type' => $exam->examType->name ?? '',
            ]);

        $results = collect();

        if ($selectedClass) {
            $students = Student::whereHas('class', fn($q) => $q->where('classes.id', $selectedClass))
                ->when($selectedSection, fn($q) => $q->whereHas('section', fn($sq) => $sq->where('sections.id', $selectedSection)))
                ->where('school_id', $schoolId)
                ->admitted()
                ->with([
                    'class',
                    'section',
                    'results.examPaper.exam.examType',
                    'results.examPaper.subject',
                    'results.markedBy'
                ])
                ->orderBy('registration_number')
                ->get();

            foreach ($students as $student) {
                $termResults = $student->results->filter(function ($result) use ($selectedExamId, $selectedTerm, $selectedAcademicYear) {
                    $exam = optional($result->examPaper->exam);
                    $examType = optional($exam->examType);

                    if ($selectedExamId) {
                        return $exam->id == $selectedExamId;
                    }

                    return $examType->code === $selectedTerm && $exam->academic_year == $selectedAcademicYear;
                });

                $resultItems = $termResults->map(function ($result) {
                    return [
                        'subject_id'       => $result->examPaper->subject_id,
                        'subject_name'     => optional($result->examPaper->subject)->name,
                        'obtained_marks'   => $result->obtained_marks,
                        'total_marks'      => $result->total_marks,
                        'percentage'       => $result->percentage,
                        'status'           => $result->status,
                        'promotion_status' => $result->promotion_status,
                        'remarks'          => $result->remarks,
                        'marked_by'        => optional($result->markedBy)->name ?? 'N/A',
                    ];
                });

                $resultData = [
                    'student' => $student,
                    'results' => $resultItems,
                    'term_has_results' => $termResults->isNotEmpty(),
                ];



                $results->push($resultData);
            }
        }

        $terms = ExamType::pluck('name', 'code');

        return Inertia::render('ExamResults/Index', [
            'classes' => $classes,
            'sections' => $sections,
            'results' => $results,
            'academicYears' => $academicYears,
            'exams' => $exams,
            'terms' => $terms,

            // Selected filters
            'selectedClass' => $selectedClass,
            'selectedSection' => $selectedSection,
            'selectedTerm' => $selectedTerm,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedExam' => $selectedExamId,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->input('school_id') ?? session('active_school_id');
        $classId = $request->input('class_id');
        $examPaperId = $request->input('exam_paper_id');

        $schools = School::select('id', 'name')->get();

        $classes = $schoolId
            ? ClassModel::forSchool($schoolId)->select('id', 'name')->get()
            : [];

        $students = ($classId)
            ? Student::where('class_id', $classId)
            ->where('school_id', $schoolId)
            ->admitted()
            ->select('id', 'name', 'registration_number')
            ->get()
            : [];

        $examPapers = ($classId)
            ? ExamPaper::whereHas('exam', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->with(['exam', 'paper', 'subject']) // To access exam.name and paper.name later
            ->get()
            : [];

        $exam = ($classId)
            ? Exam::where('class_id', $classId)
            ->where('school_id', $schoolId)
            ->latest('start_date') // optional: fetch the most recent one
            ->first()
            : null;

        $noExamExists = false;
        $noExamPapers = false;

        if ($classId) {
            if (!$exam) {
                // CASE 1: No exam exists
                $noExamExists = true;
            } elseif ($examPapers->isEmpty()) {
                // CASE 2: Exam exists but has no papers
                $noExamPapers = true;
            }
        }

        return Inertia::render('ExamResults/Create', [
            'schools' => $schools,
            'classes' => $classes,
            'students' => $students,
            'examPapers' => $examPapers,
            'selectedSchoolId' => $schoolId,
            'selectedClassId' => $classId,
            'selectedExamPaperId' => $examPaperId,
            'noExamExists' => $noExamExists,
            'noExamPapers' => $noExamPapers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd(Auth::id());
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'exam_paper_id' => 'required|exists:exam_paper,id',
            'results' => 'required|array|min:1',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.obtained_marks' => 'required|numeric|min:0',
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        foreach ($request->results as $i => $result) {
            $images = [];

            if ($request->hasFile("results.$i.images")) {
                foreach ($request->file("results.$i.images") as $imageFile) {
                    $images[] = $imageFile->store('exam-results', 'public');
                }
            }

            $examPaper = ExamPaper::find($request->exam_paper_id);
            if ($examPaper->passing_marks < $result['obtained_marks']) {
                $result['status'] = 'pass';
                $result['promotion_status'] = 'promoted';
                if ($result['obtained_marks'] > 90) {
                    $result['remarks'] = $result['remarks'] ?? 'Excellent';
                } else if ($result['obtained_marks'] > 80) {
                    $result['remarks'] = $result['remarks'] ?? 'Very Good';
                } else if ($result['obtained_marks'] > 70) {
                    $result['remarks'] = $result['remarks'] ?? 'Good';
                } else {
                    $result['remarks'] = $result['remarks'] ?? 'Pass';
                }
            } else {
                $result['promotion_status'] = 'failed';
                $result['status'] = 'fail';
                $result['remarks'] = $result['remarks'] ?? 'Marks not passed.';
            }
            $examResult = ExamResult::updateOrCreate(
                [
                    'exam_paper_id' => $request->exam_paper_id,
                    'student_id' => $result['student_id'],
                ],
                [
                    'obtained_marks' => $result['obtained_marks'],
                    'total_marks' => $result['total_marks'],
                    'percentage' => ($result['total_marks'] > 0)
                        ? ($result['obtained_marks'] / $result['total_marks']) * 100
                        : null,
                    'status' => $result['status'],
                    'promotion_status' => $result['promotion_status'],
                    'remarks' => $result['remarks'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );

            if (!empty($images)) {
                foreach ($images as $path) {
                    $examResult->images()->create([
                        'path' => $path,
                    ]);
                }
            }
        }

        return redirect()->route('exam-results.index')->with('success', 'Exam results saved successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('resultspromotions::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($examPaperId)
    {
        $examPaper = ExamPaper::with('class', 'exam')->findOrFail($examPaperId);

        $schoolId = session('active_school_id');
        $classId = $examPaper->class_id;

        $students = Student::where('class_id', $classId)
            ->where('school_id', $schoolId)
            ->select('id', 'name', 'registration_number')
            ->get();

        $existingResults = ExamResult::where('exam_paper_id', $examPaperId)
            ->get()
            ->keyBy('student_id');

        $results = $students->map(function ($student) use ($existingResults) {
            $result = $existingResults->get($student->id);

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'registration_number' => $student->registration_number,
                'obtained_marks' => $result->obtained_marks ?? '',
                'total_marks' => $result->total_marks ?? '',
                'status' => $result->status ?? 'pass',
                'promotion_status' => $result->promotion_status ?? 'pending',
                'remarks' => $result->remarks ?? '',
            ];
        });

        return Inertia::render('ExamResults/Edit', [
            'examPaper' => [
                'id' => $examPaper->id,
                'name' => $examPaper->name,
            ],
            'class' => [
                'id' => $examPaper->class->id,
                'name' => $examPaper->class->name,
            ],
            'results' => $results,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $examPaperId)
    {
        $request->validate([
            'results' => 'required|array|min:1',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.obtained_marks' => 'required|numeric|min:0',
            'results.*.total_marks' => 'required|numeric|min:1',
            'results.*.status' => 'required|in:pass,fail,absent',
            'results.*.promotion_status' => 'required|in:promoted,failed,pending',
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        foreach ($request->results as $result) {
            ExamResult::updateOrCreate(
                [
                    'exam_paper_id' => $examPaperId,
                    'student_id' => $result['student_id'],
                ],
                [
                    'obtained_marks' => $result['obtained_marks'],
                    'total_marks' => $result['total_marks'],
                    'percentage' => ($result['total_marks'] > 0)
                        ? ($result['obtained_marks'] / $result['total_marks']) * 100
                        : null,
                    'status' => $result['status'],
                    'promotion_status' => $result['promotion_status'],
                    'remarks' => $result['remarks'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('exam-results.index')->with('success', 'Exam results updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
