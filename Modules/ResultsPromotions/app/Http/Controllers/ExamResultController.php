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

    public function index(Request $request)
    {
        $schoolId = session('active_school_id');

        // Fetch dropdown data
        $classes = ClassModel::whereHas('schools', fn($q) => $q->where('schools.id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Filters from request
        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedTerm = $request->input('term', '1st_term');

        // Prepare results
        $results = collect();

        if ($selectedClass) {
            $students = Student::whereHas('class', fn($q) => $q->where('classes.id', $selectedClass))
                ->when($selectedSection, fn($q) => $q->whereHas('section', fn($sq) => $sq->where('sections.id', $selectedSection)))
                ->where('school_id', $schoolId)
                ->admitted()  // assuming this scope filters admitted students
                ->with([
                    'class',
                    'section',
                    'results.examPaper.exam.examType',  // you have this
                    'results.examPaper.subject'         // ensure subject is loaded so you can show subject
                ])
                ->orderBy('registration_number')
                ->get();

            foreach ($students as $student) {
                // Filter student's results by term
                $termResults = $student->results->filter(function ($result) use ($selectedTerm) {
                    return optional($result->examPaper->exam->examType)->code === $selectedTerm;
                });

                // Build result items for each result
                $resultItems = $termResults->map(function ($result) {
                    return [
                        'subject_id'      => $result->examPaper->subject_id,
                        'subject_name'    => optional($result->examPaper->subject)->name,
                        'obtained_marks'  => $result->obtained_marks,
                        'total_marks'     => $result->total_marks,
                        'exam_paper_title' => optional($result->examPaper)->title,
                        // you can add more fields if needed (date, etc.)
                    ];
                });

                $obtainedTotal = $termResults->sum('obtained_marks');
                $possibleTotal = $termResults->sum('total_marks');

                $percentage = $possibleTotal > 0
                    ? round(($obtainedTotal / $possibleTotal) * 100, 2)
                    : 0;

                $results->push([
                    'student'                => $student,
                    'results'                => $resultItems,
                    'total_obtained_marks'   => $obtainedTotal,
                    'total_possible_marks'   => $possibleTotal,
                    'percentage'             => $percentage,
                    'term_has_results'       => $termResults->isNotEmpty(),  // whether any results exist for that term
                ]);
            }
        }

        $terms = ExamType::pluck('name', 'code');

        return Inertia::render('ExamResults/Index', [
            'classes'          => $classes,
            'sections'         => $sections,
            'results'          => $results,
            'selectedClass'    => $selectedClass,
            'selectedSection'  => $selectedSection,
            'selectedTerm'     => $selectedTerm,
            'terms'            => $terms,
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
