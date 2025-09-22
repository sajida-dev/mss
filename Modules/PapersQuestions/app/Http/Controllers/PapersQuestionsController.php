<?php

namespace Modules\PapersQuestions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\PapersQuestions\App\Models\Question;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Modules\ClassesSections\App\Models\Subject;
use Modules\Schools\App\Models\School;

class PapersQuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $schoolId = session('active_school_id');
            $query = Paper::query()->with([
                'class:id,name',
                'section:id,name',
                'teacher.user:id,name',
                'subject:id,name',
                'academicYear:id,name',
            ])
                ->withCount('questions');

            // Filter by selected school
            if ($schoolId) {
                $query->whereHas('class.schools', function ($q) use ($schoolId) {
                    $q->where('schools.id', $schoolId);
                });
            }

            // Apply filters
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }
            if ($request->filled('published')) {
                $query->where('published', $request->published);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('title', 'like', "%{$search}%");
            }

            $perPage = $request->input('per_page', 12);
            $papers = $query->orderBy('created_at', 'desc')->paginate($perPage)->through(fn($paper) => $paper);

            // Get classes, sections, and teachers for filters
            $classes = collect();
            $sections = collect();
            $teachers = collect();

            if ($schoolId) {
                $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
                    $q->where('schools.id', $schoolId);
                })->get(['id', 'name']);

                // Get sections that are associated with classes in this school through pivot tables
                $sections = Section::whereIn('id', function ($query) use ($schoolId) {
                    $query->select('class_school_sections.section_id')
                        ->from('class_school_sections')
                        ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                        ->where('class_schools.school_id', $schoolId);
                })->get(['id', 'name']);

                // Get teachers with their names from users table
                $teachers = Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                    ->where('teachers.school_id', $schoolId)
                    ->orderBy('users.name')
                    ->get(['teachers.id', 'users.name']);
            }

            return Inertia::render('PapersQuestions/Index', [
                'papers' => $papers,
                'classes' => $classes,
                'sections' => $sections,
                'teachers' => $teachers,
                'filters' => $request->only(['class_id', 'section_id', 'teacher_id', 'published', 'search']),
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to load papers. Please try again.'])->withInput();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schoolId = session('active_school_id');
        $user = Auth::user();

        $classes = collect();
        $sections = collect();
        $teachers = collect();
        $subjects = collect();
        $userRole = null;
        $teacherSubjects = collect();

        if ($schoolId) {
            // Get user role
            $userRole = $user->roles->first()?->name;

            // Get classes for the school
            $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->get(['id', 'name']);

            // Get sections that are associated with classes in this school through pivot tables
            $sections = Section::whereIn('id', function ($query) use ($schoolId) {
                $query->select('class_school_sections.section_id')
                    ->from('class_school_sections')
                    ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                    ->where('class_schools.school_id', $schoolId);
            })->get(['id', 'name']);

            // Get teachers with their names from users table
            $teachers = Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.school_id', $schoolId)
                ->orderBy('users.name')
                ->get(['teachers.id', 'users.name']);

            // Get subjects based on user role
            if (in_array($userRole, ['admin', 'superadmin'])) {
                // Admin/SuperAdmin can see all subjects
                $subjects = \Modules\ClassesSections\App\Models\Subject::orderBy('name')->get(['id', 'name', 'code']);
            } else {
                // Teachers can only see their assigned subjects
                $teacher = Teacher::where('user_id', $user->id)->where('school_id', $schoolId)->first();
                if ($teacher) {
                    $teacherSubjects = $teacher->subjects()->orderBy('name')->get(['id', 'name', 'code']);
                }
            }
        }

        return Inertia::render('PapersQuestions/Create', [
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'teacherSubjects' => $teacherSubjects,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Get subjects by class for dynamic loading
     */
    public function getSubjectsByClass($classId)
    {
        $schoolId = session('active_school_id');

        // Fallback: if no school is set in session, try to set one automatically
        if (!$schoolId) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->hasRole('superadmin')) {
                $firstSchool = School::first();
                if ($firstSchool) {
                    $schoolId = $firstSchool->id;
                    session(['active_school_id' => $schoolId]);
                }
            } elseif ($user->hasRole('admin')) {
                $userSchools = $user->schools;
                if ($userSchools->count() > 0) {
                    $schoolId = $userSchools->first()->id;
                    session(['active_school_id' => $schoolId]);
                }
            }
        }

        $subjects = collect();

        if ($schoolId) {
            $class = ClassModel::findOrFail($classId);

            // Check if class belongs to the school
            if ($class->schools()->where('schools.id', $schoolId)->exists()) {
                // Get subjects for the class that are assigned to this school
                $subjects = $class->subjects()
                    ->wherePivot('school_id', $schoolId)
                    ->get(['subjects.id', 'subjects.name', 'subjects.code']);
            }
        }

        return response()->json($subjects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->roles->first()?->name;
        $schoolId = session('active_school_id');

        // Determine subject_id based on user role
        $subjectId = null;
        if (in_array($userRole, ['admin', 'superadmin'])) {
            // Admin/SuperAdmin must select a subject
            $subjectId = $request->subject_id;
        } else {
            // For teachers, auto-assign their subject based on class
            $teacher = Teacher::where('user_id', $user->id)->where('school_id', $schoolId)->first();
            if ($teacher) {
                $teacherSubject = $teacher->subjects()
                    ->wherePivot('class_id', $request->class_id)
                    ->first();
                $subjectId = $teacherSubject ? $teacherSubject->id : null;
            }
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'published' => 'boolean',
            'total_marks' => 'nullable|integer|min:0',
            'time_duration' => 'nullable|integer|min:1',
            'subject_id' => in_array($userRole, ['admin', 'superadmin']) ? 'required|exists:subjects,id' : 'nullable',
            'instructions' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,short_answer,long_answer,essay,numerical',
            'questions.*.section' => 'required|in:objective,short_questions,long_questions,essay',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.question_number' => 'nullable|integer|min:1',
            'questions.*.options' => 'nullable|array',
            'questions.*.answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate that teacher has access to the subject for this class
        if (!in_array($userRole, ['admin', 'superadmin'])) {
            if (!$subjectId) {
                return back()->withErrors(['error' => 'You are not assigned to teach any subject for this class.'])->withInput();
            }
        }
        $class = ClassModel::find($request->class_id);
        $subject = Subject::find($subjectId);

        $titleParts = [];

        if ($subject) {
            $titleParts[] = $subject->name;
        }


        if ($class) {
            $titleParts[] = $class->name;
        }
        $titleParts[] = AcademicYear::find(session('active_academic_year_id'))?->name;
        $generatedTitle = implode(' - ', $titleParts);
        try {
            DB::beginTransaction();

            $paper = Paper::create([
                'title' => $generatedTitle,
                'class_id' => $request->class_id,
                'school_id' => $schoolId,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id,
                'subject_id' => $subjectId,
                'published' => $request->published ?? false,
                'total_marks' => $request->total_marks,
                'time_duration' => $request->time_duration ?? 120,
                'instructions' => $request->instructions,
                'academic_year_id' => session('active_academic_year_id'),
            ]);

            // Create questions
            foreach ($request->questions as $questionData) {
                Question::create([
                    'paper_id' => $paper->id,
                    'text' => $questionData['text'],
                    'type' => $questionData['type'],
                    'section' => $questionData['section'],
                    'marks' => $questionData['marks'],
                    'question_number' => $questionData['question_number'] ?? null,
                    'options' => $questionData['options'] ?? null,
                    'answer' => $questionData['answer'] ?? null,
                ]);
            }

            DB::commit();

            // Return redirect to show the created paper
            return redirect()->route('papersquestions.show', $paper->id)
                ->with('success', 'Paper created successfully with ' . count($request->questions) . ' questions.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create paper. Please try again.'])->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $paper = Paper::with(['class', 'section', 'teacher', 'questions', 'subject', 'academicYear'])->find($id);

        return Inertia::render('PapersQuestions/Show', [
            'paper' => $paper,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $paper = Paper::with(['questions'])->findOrFail($id);

        $schoolId = session('active_school_id');

        $classes = collect();
        $sections = collect();
        $teachers = collect();

        if ($schoolId) {
            $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get sections that are associated with classes in this school through pivot tables
            $sections = Section::whereIn('id', function ($query) use ($schoolId) {
                $query->select('class_school_sections.section_id')
                    ->from('class_school_sections')
                    ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                    ->where('class_schools.school_id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get teachers with their names from users table
            $teachers = Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.school_id', $schoolId)
                ->orderBy('users.name')
                ->get(['teachers.id', 'users.name']);
        }

        return Inertia::render('PapersQuestions/Edit', [
            'paper' => $paper,
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paper = Paper::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'published' => 'boolean',
            'total_marks' => 'nullable|integer|min:0',
            'time_duration' => 'nullable|integer|min:1',
            'subject_name' => 'nullable|string|max:255',
            'subject_code' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,short_answer,long_answer,essay,numerical',
            'questions.*.section' => 'required|in:objective,short_questions,long_questions,essay',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.question_number' => 'nullable|integer|min:1',
            'questions.*.clo' => 'nullable|string|max:50',
            'questions.*.options' => 'nullable|array',
            'questions.*.answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $paper->update([
                'title' => $request->title,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id,
                'published' => $request->published ?? false,
                'total_marks' => $request->total_marks,
                'time_duration' => $request->time_duration ?? 120,
                'course_name' => $request->course_name,
                'course_code' => $request->course_code,
                'program' => $request->program,
                'semester' => $request->semester,
                'session' => $request->session,
                'exam_date' => $request->exam_date,
                'instructions' => $request->instructions,
            ]);

            // Delete existing questions and create new ones
            $paper->questions()->delete();

            foreach ($request->questions as $questionData) {
                Question::create([
                    'paper_id' => $paper->id,
                    'text' => $questionData['text'],
                    'type' => $questionData['type'],
                    'section' => $questionData['section'],
                    'marks' => $questionData['marks'],
                    'question_number' => $questionData['question_number'] ?? null,
                    'clo' => $questionData['clo'] ?? null,
                    'options' => $questionData['options'] ?? null,
                    'answer' => $questionData['answer'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('papersquestions.index')
                ->with('success', 'Paper updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('papersquestions.index')->withErrors(['error' => 'Failed to update paper. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paper = Paper::findOrFail($id);

        // Only allow deletion if paper is not published
        if ($paper->published) {
            return back()->withErrors(['error' => 'Cannot delete a published paper.']);
        }

        $paper->delete();

        return redirect()->route('papersquestions.index')
            ->with('success', 'Paper deleted successfully.');
    }

    /**
     * Toggle the publish status of a paper
     */
    public function togglePublish($id)
    {
        $paper = Paper::findOrFail($id);
        $paper->published = !$paper->published;
        $paper->save();

        $status = $paper->published ? 'published' : 'unpublished';
        return redirect()->route('papersquestions.index')
            ->with('success', "Paper {$status} successfully.");
    }
}
