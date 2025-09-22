<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
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
    public function index(Request $request)
    {
        $schoolId = session('active_school_id');

        // Filters
        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedAcademicYear = $request->input('academic_year_id');
        $selectedTerm = $request->input('term');
        $selectedExamId = $request->input('exam_id');

        // Load classes & sections for filter dropdowns
        $classes = ClassModel::whereHas('schools', fn($q) => $q->where('schools.id', $schoolId))
            ->get(['id', 'name']);
        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->get(['id', 'name']);

        // Academic years that have exams matching the filters
        $academicYears = AcademicYear::whereHas('exams', function ($q) use ($schoolId, $selectedTerm, $selectedAcademicYear) {
            $q->where('school_id', $schoolId);

            if ($selectedAcademicYear) {
                $q->where('academic_year_id', $selectedAcademicYear);
            }

            if ($selectedTerm) {
                $q->whereHas('examType', fn($q2) => $q2->where('code', $selectedTerm));
            }
        })
            ->orderByDesc('start_date')
            ->get(['id', 'name']);

        // Exams list filtered (optional, for dropdown or detail)
        $exams = Exam::where('school_id', $schoolId)
            ->when($selectedClass, fn($q) => $q->where('class_id', $selectedClass))
            ->when($selectedSection, fn($q) => $q->where('section_id', $selectedSection))
            ->when($selectedAcademicYear, fn($q) => $q->where('academic_year_id', $selectedAcademicYear))
            ->when($selectedTerm, fn($q) => $q->whereHas('examType', fn($q2) => $q2->where('code', $selectedTerm)))
            ->with('examType')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'exam_type' => $exam->examType?->name ?? '',
                'academic_year_id' => $exam->academic_year_id,
            ]);

        // Prepare grouped student results data
        $studentsGrouped = collect();

        if ($selectedClass && $selectedAcademicYear) {
            // Fetch students with required relations
            $students = Student::whereHas('class', fn($q) => $q->where('classes.id', $selectedClass))
                ->when($selectedSection, fn($q) => $q->whereHas('section', fn($sq) => $sq->where('sections.id', $selectedSection)))
                ->where('school_id', $schoolId)
                ->admitted()
                ->with([
                    'results.examPaper.exam.examType',
                    'results.examPaper.subject',
                    'termResults' => fn($q) => $q->where('academic_year_id', $selectedAcademicYear),
                    'academicResults' => fn($q) => $q->where('academic_year_id', $selectedAcademicYear),
                ])
                ->orderBy('registration_number')
                ->get();

            $allExamTypesInYear = ExamType::whereHas('exams', fn($q) => $q->where('academic_year_id', $selectedAcademicYear))
                ->get();
            // dd($allExamTypesInYear);
            foreach ($students as $student) {
                $grouped = [
                    'year_name' => $student->academicResults->first()?->academicYear?->name ?? AcademicYear::find($selectedAcademicYear)?->name,
                    'terms' => [],
                    'all_terms_completed' => false,
                    'academic_result' => null,
                ];

                // For each exam type expected in that academic year
                foreach ($allExamTypesInYear as $examType) {
                    $termCode = $examType->code;
                    $termName = $examType->name;

                    // Get exam paper result items for this student & this term
                    $items = $student->results
                        ->filter(
                            fn($res) =>
                            $res->examPaper->exam->exam_type_id == $examType->id
                                && $res->examPaper->exam->academic_year_id == $selectedAcademicYear
                        )->map(fn($res) => [
                            'subject_id' => $res->examPaper->subject_id,
                            'subject_name' => $res->examPaper->subject?->name ?? '',
                            'obtained_marks' => $res->obtained_marks,
                            'total_marks' => $res->total_marks,
                            'percentage' => $res->percentage,
                            'status' => $res->status,
                            'remarks' => $res->remarks,
                        ])->values();

                    // Find if there is a term result summary
                    $tr = $student->termResults
                        ->firstWhere('exam_type_id', $examType->id);

                    $grouped['terms'][$termCode] = [
                        'term_name' => $termName,
                        'exam_type_id' => $examType->id,
                        'items' => $items,
                        'term_result' => $tr ? [
                            'total_marks' => $tr->total_marks,
                            'obtained_marks' => $tr->obtained_marks,
                            'overall_percentage' => $tr->overall_percentage,
                            'subjects_passed' => $tr->subjects_passed,
                            'subjects_failed' => $tr->subjects_failed,
                            'grade' => $tr->grade,
                            'remarks' => $tr->remarks,
                            'term_status' => $tr->term_status,
                        ] : null,
                    ];
                }

                // Check if all terms have term_result (i.e. summaries present)
                $completedTermsCount = collect($grouped['terms'])
                    ->filter(fn($t) => !is_null($t['term_result']))
                    ->count();
                $expectedTermsCount = $allExamTypesInYear->count();

                if ($expectedTermsCount > 0 && $completedTermsCount === $expectedTermsCount) {
                    $grouped['all_terms_completed'] = true;

                    // fetch academic result
                    $ar = $student->academicResults->first();
                    if ($ar) {
                        $grouped['academic_result'] = [
                            'overall_percentage' => $ar->overall_percentage,
                            'cumulative_gpa' => $ar->cumulative_gpa,
                            'final_grade' => $ar->final_grade,
                            'promotion_status' => $ar->promotion_status,
                        ];
                    }
                }

                $studentsGrouped->push([
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'registration_number' => $student->registration_number,
                        'class_name' => $student->class_name ?? '',
                    ],
                    'grouped_terms' => $grouped,
                ]);
            }
        }

        $terms = ExamType::pluck('name', 'code');

        return Inertia::render('ExamResults/Index', [
            'classes' => $classes,
            'sections' => $sections,
            'academicYears' => $academicYears,
            'exams' => $exams,
            'terms' => $terms,
            'studentsGrouped' => $studentsGrouped,
            'selectedClass' => $selectedClass,
            'selectedSection' => $selectedSection,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedTerm' => $selectedTerm,
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
