<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamType;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\Teacher;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $role = $user->roles[0]->name;
        $schoolId = session('active_school_id');

        $exams = Exam::with('examType', 'class', 'section', 'school')
            ->withCount('examPapers')
            ->get()
            ->map(function ($exam) {
                $exam->can_be_deleted = $exam->exam_papers_count === 0;
                return $exam;
            });
        $examTypes = ExamType::all();

        if ($role === 'superadmin') {
            // Superadmin: get all classes for the selected school
            $classes = ClassModel::forSchool($schoolId)
                ->select('id', 'name')
                ->get()
                ->map(function ($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                    ];
                })
                ->values();
        } else if ($role === 'teacher') {
            // Teacher: get the class assigned to them via the teachers table
            $teacher = Teacher::where('user_id', $user->id)
                ->where('school_id', $schoolId)
                ->first();

            $classes = [];

            if ($teacher && $teacher->class_id) {
                $class = ClassModel::find($teacher->class_id);
                if ($class) {
                    $classes[] = [
                        'id' => $class->id,
                        'name' => $class->name,
                    ];
                }
            }
        } else {
            // Other roles - optional, return empty or handle accordingly
            $classes = collect();
        }

        return Inertia::render('Exams/ExamsIndex', [
            'examTypes' => $examTypes,
            'exams' => $exams,
            'classes' => $classes,
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Exams/Create', [
            'examTypes' => ExamType::all(),
            'schools'   => School::all(),
            'classes'   => ClassModel::classSchools()->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_type_id'  => 'required|exists:exam_types,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'result_entry_deadline' => 'required|date|after:end_date',
            'instructions'  => 'nullable|string',
        ]);

        $data['school_id'] = session('active_school_id');
        $examType = ExamType::find($data['exam_type_id']);

        try {
            DB::transaction(function () use ($data, $examType) {
                foreach ($data['class_ids'] as $classId) {
                    $class = ClassModel::find($classId);
                    $sections = $class->sections()->get();

                    foreach ($sections as $section) {
                        $startDate = Carbon::parse($data['start_date']);
                        $examData = [...$data];
                        $examData['section_id'] = $section->id;
                        $examData['class_id'] = $classId;
                        $examData['academic_year'] = $startDate->format('Y-Y');
                        $examData['title'] = "{$class->name} - {$section->name} | {$examType->name} Exam ({$examData['academic_year']})";

                        Exam::updateOrCreate(
                            [
                                'class_id' => $examData['class_id'],
                                'section_id' => $examData['section_id'],
                                'exam_type_id' => $examData['exam_type_id'],
                            ],
                            $examData
                        );
                    }
                }
            });

            return redirect()->route('exams.index')->with('success', 'Exam created for all selected classes.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
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
    public function edit(Exam $exam)
    {
        return Inertia::render('Exams/Edit', [
            'exam'      => $exam,
            'examTypes' => ExamType::all(),
            'schools'   => School::all(),
            'classes'   => ClassModel::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {

        $data = $request->validate([
            'exam_type_id'  => 'required|exists:exam_types,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'nullable|exists:sections,id',
            'academic_year' => 'required|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'instructions'  => 'nullable|string',
        ]);
        $data['school_id'] = session('active_school_id');
        try {
            DB::transaction(function () use ($exam, $data) {
                $exam->update($data);
            });
            return redirect()->route('exams.index')->with('success', 'Update exam successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam)
    {
        try {
            DB::transaction(function () use ($exam) {
                $exam->delete();
            });
            return redirect()->route('exams.index')->with('success', 'Deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

    public function extendDeadline(Request $request, Exam $exam)
    {
        $request->validate([
            'result_entry_deadline' => 'required|date|after_or_equal:today',
        ]);
        $deadline = Carbon::parse($request->result_entry_deadline)->timezone('UTC');
        $exam->update([
            'result_entry_deadline' => $deadline,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('exams.index')->with('success', 'Deadline extended successfully.');
    }
}
