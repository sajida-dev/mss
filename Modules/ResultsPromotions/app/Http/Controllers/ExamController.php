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
                        $examTypeCode = $examType->code;
                        $examData = [...$data];
                        $examData['section_id'] = $section->id;
                        $examData['class_id'] = $classId;

                        // Academic Year Calculation
                        if ($examTypeCode === '1st_term') {
                            $academicYear = $startDate->format('Y') . '-' . $startDate->copy()->addYear()->format('Y');
                        } else {
                            // Try finding 1st term exam to get academic year
                            $firstTermExam = Exam::where('class_id', $classId)
                                ->where('section_id', $section->id)
                                ->where('school_id', $data['school_id'])
                                ->whereHas('examType', fn($q) => $q->where('code', '1st_term'))
                                ->orderByDesc('created_at')
                                ->first();

                            if (!$firstTermExam) {
                                throw new \Exception("Cannot create {$examType->name} for {$class->name} - {$section->name} without creating 1st Term first.");
                            }

                            $academicYear = $firstTermExam->academic_year;

                            // Optional: Enforce 2nd term must have 1st, 3rd must have 1st and 2nd
                            if ($examTypeCode === '2nd_term') {
                                $termCheck = Exam::where('academic_year', $academicYear)
                                    ->where('class_id', $classId)
                                    ->where('section_id', $section->id)
                                    ->whereHas('examType', fn($q) => $q->where('code', '1st_term'))
                                    ->exists();

                                if (!$termCheck) {
                                    throw new \Exception("Cannot create 2nd Term without 1st Term for {$class->name} - {$section->name}.");
                                }
                            } elseif ($examTypeCode === '3rd_term') {
                                $termCheck = Exam::where('academic_year', $academicYear)
                                    ->where('class_id', $classId)
                                    ->where('section_id', $section->id)
                                    ->whereHas('examType', fn($q) => $q->whereIn('code', ['1st_term', '2nd_term']))
                                    ->count();

                                if ($termCheck < 2) {
                                    throw new \Exception("Cannot create 3rd Term without both 1st and 2nd Terms for {$class->name} - {$section->name}.");
                                }
                            }
                        }

                        $examData['academic_year'] = $academicYear;
                        $examData['title'] = "{$class->name} - {$section->name} | {$examType->name} Exam ({$academicYear})";

                        // Avoid duplicate for same class/section/exam type/academic year
                        Exam::updateOrCreate(
                            [
                                'class_id' => $examData['class_id'],
                                'section_id' => $examData['section_id'],
                                'exam_type_id' => $examData['exam_type_id'],
                                'academic_year' => $academicYear,
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
