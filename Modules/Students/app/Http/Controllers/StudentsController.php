<?php

namespace Modules\Students\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Admissions\App\Http\Requests\UpdateStudentRequest;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get selected school from session
        $selectedSchoolId = session('active_school_id');
        $query = Student::with(['school', 'class'])
            ->where('status', 'admitted');

        // Always filter by selected school if set
        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        } elseif ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('b_form_number', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('class_shift')) {
            $query->where('class_shift', $request->class_shift);
        }

        $perPage = $request->input('per_page', 12);
        $students = $query->orderBy('name')->paginate($perPage);

        // Fetch all schools
        $schools = School::orderBy('name')->get(['id', 'name']);

        // Fetch classes for selected school using Eloquent
        $classes = collect();
        $schoolIdForClasses = $selectedSchoolId ?: $request->school_id;
        if ($schoolIdForClasses) {
            $school = School::with(['classes' => function ($q) {
                $q->orderBy('name');
            }])->find($schoolIdForClasses);
            $classes = $school ? $school->classes : collect();
        }

        // Fetch sections for selected class using Eloquent
        $sections = collect();
        if ($request->filled('class_id') && $schoolIdForClasses) {
            $school = School::with(['classes.sections'])->find($schoolIdForClasses);
            if ($school) {
                $class = $school->classes->where('id', $request->class_id)->first();
                $sections = $class && $class->relationLoaded('sections') ? $class->sections->sortBy('name')->values() : collect();
            }
        }

        return Inertia::render('Students/Index', [
            'students' => $students,
            'schools' => $schools,
            'classes' => $classes,
            'sections' => $sections,
            'filters' => $request->only(['search', 'class_id', 'school_id', 'section_id', 'gender', 'class_shift']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Students/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $student = Student::with(['school', 'class'])->findOrFail($id);
        return Inertia::render('Students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = Student::with(['school', 'class'])->findOrFail($id);
        $classes = ClassModel::orderBy('name')->get(['id', 'name']);
        $schools = School::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Students/Edit', [
            'student' => $student,
            'classes' => $classes,
            'schools' => $schools,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $student = Student::findOrFail($id);
                $validated = $request->validated();

                // Handle file upload
                if ($request->hasFile('profile_photo_path')) {
                    // Remove old image if it exists
                    if ($student->profile_photo_path && Storage::disk('public')->exists($student->profile_photo_path)) {
                        Storage::disk('public')->delete($student->profile_photo_path);
                    }
                    $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
                    $validated['profile_photo_path'] = $path;
                } else {
                    // If not uploading a new file, keep the old path
                    $validated['profile_photo_path'] = $student->profile_photo_path;
                }

                $student->update($validated);
                // Broadcast::event('student.updated', $student);
                return redirect()->route('students.index')->with('success', 'Student updated successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to update student admission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $id,
                'request_data' => $request->except(['profile_photo_path'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update student. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
