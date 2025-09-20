<?php

namespace Modules\ClassesSections\App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ClassesSections\App\Models\Subject;
use Modules\ClassesSections\App\Models\ClassModel;
use App\Models\User;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Modules\ClassesSections\app\Models\ClassSubject;
use Modules\Teachers\Models\ClassSubjectTeacher;
use Modules\Teachers\Models\Teacher;

class SubjectsController extends Controller
{
    /**
     * Helper method to set school context for role operations
     */
    private function setSchoolContextForRoles($schoolId)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
    }

    /**
     * Helper method to clear school context
     */
    private function clearSchoolContext()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    public function index(Request $request)
    {
        $subjects = Subject::query()->orderBy('name')->get();

        $activeSchoolId = session('active_school_id');


        $classes = ClassModel::forSchool($activeSchoolId)->get();

        $this->setSchoolContextForRoles($activeSchoolId);

        $teachers = User::whereHas('teacher', function ($query) use ($activeSchoolId) {
            $query->forSchool($activeSchoolId);
        })->whereHas('roles', function ($query) {
            $query->whereIn('name', ['teacher', 'principal']);
        })->with(['teacher.class', 'roles'])->get();

        // If no teachers found with school context, try without it
        if ($teachers->count() === 0) {
            Log::warning('No teachers found with school context, trying without context', [
                'active_school_id' => $activeSchoolId
            ]);

            $this->clearSchoolContext();

            // Try getting teachers without school context
            $teachers = User::whereHas('teacher', function ($query) use ($activeSchoolId) {
                $query->forSchool($activeSchoolId);
            })->whereHas('roles', function ($query) {
                $query->whereIn('name', ['teacher', 'principal']);
            })->with(['teacher.class', 'roles'])->get();

            Log::info('Teachers found without school context', [
                'teachers_count' => $teachers->count()
            ]);
        }

        // Add debugging
        Log::info('Teachers loaded for subject assignment', [
            'active_school_id' => $activeSchoolId,
            'teachers_count' => $teachers->count(),
            'teachers' => $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'roles' => $teacher->roles->pluck('name'),
                    'teacher_record' => $teacher->teacher ? [
                        'id' => $teacher->teacher->id,
                        'school_id' => $teacher->teacher->school_id,
                        'status' => $teacher->teacher->status,
                        'class_id' => $teacher->teacher->class_id,
                        'class_name' => $teacher->teacher->class ? $teacher->teacher->class->name : null
                    ] : null
                ];
            })
        ]);

        // Clear school context
        $this->clearSchoolContext();

        // Get assignments data using Eloquent
        $assignments = ClassModel::getWithSubjectsForSchool($activeSchoolId)
            ->flatMap(function ($class) {
                return $class->subjects->map(function ($subject) use ($class) {
                    return [
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'subject_code' => $subject->code,
                        'school_id' => $subject->pivot->school_id
                    ];
                });
            });

        // Load teacher assignments using Eloquent model
        $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
            ->forSchool($activeSchoolId)
            ->get()
            ->map(function ($assignment) {
                return [
                    'teacher_id' => $assignment->teacher_id,
                    'teacher_user_id' => $assignment->teacher->user_id,
                    'teacher_name' => $assignment->teacher->user->name,
                    'teacher_class_id' => $assignment->teacher->class_id,
                    'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : null,
                    'class_id' => $assignment->class_id,
                    'class_name' => $assignment->class->name,
                    'subject_id' => $assignment->subject_id,
                    'subject_name' => $assignment->subject->name,
                    'subject_code' => $assignment->subject->code,
                    'school_id' => $assignment->school_id
                ];
            });

        // Debug logging for teacher assignments
        Log::info('Teacher assignments loaded in index', [
            'active_school_id' => $activeSchoolId,
            'assignments_count' => $teacherAssignments->count(),
            'assignments' => $teacherAssignments->toArray()
        ]);

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
            'classes' => $classes,
            'teachers' => $teachers,
            'assignments' => $assignments,
            'teacherAssignments' => $teacherAssignments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z]{3,5}\d{3}$/', // e.g. MTH101, MATH101, PHYCH101
                Rule::unique('subjects', 'code')->whereNull('deleted_at') // ignore soft-deleted
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            Log::info('Subject creation request received', $request->all());

            $existing = Subject::withTrashed()->where('code', $request->code)->first();

            if ($existing && $existing->trashed()) {
                $existing->restore();
                $existing->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);

                DB::commit();

                return redirect()->back()->with('success', 'Previously deleted subject restored and updated!');
            }

            $subject = Subject::create($validated);

            DB::commit();

            Log::info('Subject created successfully', ['subject_id' => $subject->id]);

            return redirect()->back()->with('success', 'Subject created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating subject', ['error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Failed to create subject. Please try again.']);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z]{3,5}\d{3}$/', // e.g. MTH101, MATH101, PHYCH101
                Rule::unique('subjects', 'code')->ignore($id)->whereNull('deleted_at')
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            Log::info('Subject update request received', [
                'subject_id' => $id,
                'data' => $request->all()
            ]);

            $subject = Subject::withTrashed()->findOrFail($id);

            if ($subject->trashed()) {
                $subject->restore();
                Log::warning('Soft-deleted subject restored during update.', ['subject_id' => $id]);
            }

            $subject->update($validated);

            DB::commit();

            Log::info('Subject updated successfully', ['subject_id' => $subject->id]);

            return redirect()->back()->with('success', 'Subject updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating subject', [
                'subject_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to update subject. Please try again.']);
        }
    }


    public function destroy($id)
    {
        try {
            Log::info('Subject deletion request received', ['subject_id' => $id]);
            $isSubjectAssigned = ClassSubject::where('subject_id', $id)->exists();
            if ($isSubjectAssigned) {
                return redirect()->back()->with('error', 'Subject is assigned to a class. Please unassign it first.');
            }
            $isAssignedToTeacher = ClassSubjectTeacher::where('subject_id', $id)->exists();
            if ($isAssignedToTeacher) {
                return redirect()->back()->with('error', 'Subject is assigned to a teacher. Please unassign it first.');
            }
            $subject = Subject::findOrFail($id);
            $subject->delete();

            Log::info('Subject deleted successfully', ['subject_id' => $subject->id]);

            return redirect()->back()->with('success', 'Subject deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete subject. Please try again.']);
        }
    }

    public function show($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            return Inertia::render('Subjects/Show', [
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Subject not found.']);
        }
    }

    public function create()
    {
        return Inertia::render('Subjects/Create');
    }

    public function edit($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            return Inertia::render('Subjects/Edit', [
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Subject not found.']);
        }
    }

    public function assignToClass(Request $request, $classId)
    {
        $request->validate([
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        try {
            // Get the active school ID
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->withErrors(['error' => 'No school is currently selected.']);
            }

            // First, remove all existing assignments for this class
            ClassSubject::where('class_id', $classId)
                ->where('school_id', $activeSchoolId)
                ->delete();
            $academicYearId = session('active_academic_year_id');
            // Then, create new assignments
            $assignments = [];
            foreach ($request->subject_ids as $subjectId) {
                $assignments[] = [
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'school_id' => $activeSchoolId,
                    'academic_year_id' => $academicYearId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($assignments)) {
                ClassSubject::insert($assignments);
            }

            Log::info('Subjects assigned to class', [
                'class_id' => $classId,
                'subject_ids' => $request->subject_ids,
                'school_id' => $activeSchoolId
            ]);

            return redirect()->back()->with('success', 'Subjects assigned to class successfully!');
        } catch (\Exception $e) {
            Log::error('Error assigning subjects to class', [
                'error' => $e->getMessage(),
                'class_id' => $classId
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to assign subjects: ' . $e->getMessage()]);
        }
    }

    public function assignToTeacher(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:users,id'
        ]);

        try {
            // Get the active school ID
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->withErrors(['error' => 'No school is currently selected.']);
            }

            // Set school context for role operations
            $this->setSchoolContextForRoles($activeSchoolId);

            // Check if all users have teacher or principal role
            $teachers = User::whereIn('id', $request->teacher_ids)->get();
            $nonTeachers = $teachers->filter(function ($user) {
                return !$user->hasRole(['teacher', 'principal']);
            });

            if ($nonTeachers->count() > 0) {
                $this->clearSchoolContext();
                $nonTeacherNames = $nonTeachers->pluck('name')->implode(', ');
                return redirect()->back()->withErrors(['error' => 'The following users are not teachers or principals: ' . $nonTeacherNames]);
            }

            // Clear school context after role check
            $this->clearSchoolContext();

            // First, remove all existing assignments for this class-subject combination
            ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->delete();

            // Then, create new assignments for all selected teachers
            $assignments = [];
            foreach ($request->teacher_ids as $userId) {
                // Get the teacher record for this user
                $teacher = Teacher::where('user_id', $userId)->first();

                if ($teacher) {
                    $assignments[] = [
                        'class_id' => $request->class_id,
                        'subject_id' => $request->subject_id,
                        'teacher_id' => $teacher->id,
                        'school_id' => $activeSchoolId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            if (!empty($assignments)) {
                ClassSubjectTeacher::insert($assignments);
            }

            Log::info('Teachers assigned to subject', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_ids' => $request->teacher_ids,
                'count' => count($request->teacher_ids)
            ]);

            $teacherCount = count($request->teacher_ids);
            $message = $teacherCount === 1
                ? '1 teacher assigned to subject successfully!'
                : "{$teacherCount} teachers assigned to subject successfully!";

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            // Ensure school context is cleared on error
            $this->clearSchoolContext();

            Log::error('Error assigning teachers to subject', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to assign teachers to subject: ' . $e->getMessage()]);
        }
    }

    public function getAssignmentsApi(Request $request)
    {
        try {
            $classId = $request->query('class_id');
            $subjectId = $request->query('subject_id');

            if ($classId && $subjectId) {
                // Get teachers assigned to specific class-subject combination
                $assignedTeachers = ClassSubjectTeacher::with(['teacher.user'])
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->teacher->user->id,
                            'name' => $assignment->teacher->user->name
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignedTeachers' => $assignedTeachers
                ]);
            } elseif ($classId) {
                // Get subjects assigned to specific class using Eloquent
                $assignedSubjects = ClassSubject::with('subject')
                    ->where('class_id', $classId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                            'code' => $assignment->subject->code
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignedSubjects' => $assignedSubjects
                ]);
            } else {
                // Get all assignments using Eloquent
                $assignments = ClassSubject::with(['class', 'subject'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'class_id' => $assignment->class_id,
                            'subject_id' => $assignment->subject_id,
                            'school_id' => $assignment->school_id,
                            'class_name' => $assignment->class->name,
                            'subject_name' => $assignment->subject->name,
                            'subject_code' => $assignment->subject->code,
                            'created_at' => $assignment->created_at,
                            'updated_at' => $assignment->updated_at
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignments' => $assignments
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAssignments(Request $request)
    {
        try {
            $classId = $request->query('class_id');
            $subjectId = $request->query('subject_id');

            Log::info('getAssignments called', [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'all_params' => $request->all()
            ]);

            // Get active school ID
            $activeSchoolId = session('active_school_id');

            if ($classId && $subjectId) {
                // Get teachers assigned to specific class-subject combination using Eloquent
                $assignedTeachers = ClassSubjectTeacher::with(['teacher.user'])
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->teacher->user->id,
                            'name' => $assignment->teacher->user->name
                        ];
                    });

                Log::info('Found assigned teachers', ['count' => $assignedTeachers->count()]);

                return response()->json([
                    'success' => true,
                    'assignedTeachers' => $assignedTeachers
                ]);
            } elseif ($classId) {
                // Get subjects assigned to specific class using Eloquent
                $assignedSubjects = ClassSubject::with('subject')
                    ->where('class_id', $classId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                            'code' => $assignment->subject->code
                        ];
                    });

                Log::info('Found assigned subjects', [
                    'class_id' => $classId,
                    'count' => $assignedSubjects->count(),
                    'subjects' => $assignedSubjects->toArray()
                ]);

                return response()->json([
                    'success' => true,
                    'assignedSubjects' => $assignedSubjects
                ]);
            } else {
                // Get all assignments using Eloquent
                $assignments = ClassSubject::with(['class', 'subject'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'class_id' => $assignment->class_id,
                            'subject_id' => $assignment->subject_id,
                            'school_id' => $assignment->school_id,
                            'class_name' => $assignment->class->name,
                            'subject_name' => $assignment->subject->name,
                            'subject_code' => $assignment->subject->code,
                            'created_at' => $assignment->created_at,
                            'updated_at' => $assignment->updated_at
                        ];
                    });

                // Get teacher assignments data using Eloquent
                $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
                    ->forSchool($activeSchoolId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'teacher_id' => $assignment->teacher_id,
                            'teacher_user_id' => $assignment->teacher->user_id,
                            'teacher_name' => $assignment->teacher->user->name,
                            'teacher_class_id' => $assignment->teacher->class_id,
                            'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : null,
                            'class_id' => $assignment->class_id,
                            'class_name' => $assignment->class->name,
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject->name,
                            'subject_code' => $assignment->subject->code,
                            'school_id' => $assignment->school_id
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignments' => $assignments,
                    'teacherAssignments' => $teacherAssignments
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeAssignment(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Get the teacher record from the user_id
            $teacher = Teacher::where('user_id', $request->teacher_id)->first();

            if (!$teacher) {
                return redirect()->back()->withErrors(['error' => 'Teacher not found']);
            }

            ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $teacher->id)
                ->delete();

            return redirect()->back()->with('success', 'Assignment removed successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing assignment', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to remove assignment: ' . $e->getMessage()]);
        }
    }

    public function removeSpecificAssignment(Request $request)
    {
        Log::info('removeSpecificAssignment called', [
            'request_data' => $request->all(),
            'active_school_id' => session('active_school_id')
        ]);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Get the teacher record from the user_id
            $teacher = Teacher::where('user_id', $request->teacher_id)->first();

            Log::info('Teacher lookup result', [
                'user_id' => $request->teacher_id,
                'teacher_found' => $teacher ? true : false,
                'teacher_id' => $teacher ? $teacher->id : null
            ]);

            if (!$teacher) {
                return redirect()->back()->withErrors(['error' => 'Teacher not found.']);
            }

            // Get active school ID for additional validation
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->withErrors(['error' => 'No active school selected.']);
            }

            // Check if the assignment exists before deleting
            $existingAssignment = ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->first();

            Log::info('Assignment lookup result', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id,
                'school_id' => $activeSchoolId,
                'assignment_found' => $existingAssignment ? true : false,
                'assignment_id' => $existingAssignment ? $existingAssignment->id : null
            ]);

            if (!$existingAssignment) {
                return redirect()->back()->withErrors(['error' => 'Assignment not found for the specified class, subject, and teacher.']);
            }

            // Delete the specific assignment
            $deleted = ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->delete();

            Log::info('Specific teacher assignment removed', [
                'teacher_id' => $teacher->id,
                'teacher_user_id' => $request->teacher_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'school_id' => $activeSchoolId,
                'deleted_count' => $deleted
            ]);

            return redirect()->back()->with('success', 'Specific assignment removed successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing specific assignment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to remove specific assignment: ' . $e->getMessage()]);
        }
    }

    public function removeTeacherAssignment(Request $request)
    {
        Log::info('removeTeacherAssignment called', [
            'request_data' => $request->all(),
            'active_school_id' => session('active_school_id')
        ]);

        $request->validate([
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Get the teacher record from the user_id
            $teacher = Teacher::where('user_id', $request->teacher_id)->first();

            Log::info('Teacher lookup result', [
                'user_id' => $request->teacher_id,
                'teacher_found' => $teacher ? true : false,
                'teacher_id' => $teacher ? $teacher->id : null
            ]);

            if (!$teacher) {
                return redirect()->back()->withErrors(['error' => 'Teacher not found.']);
            }

            // Get active school ID for additional validation
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->withErrors(['error' => 'No active school selected.']);
            }

            // Check existing assignments before deleting
            $existingAssignments = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->get();

            Log::info('Existing assignments found', [
                'teacher_id' => $teacher->id,
                'school_id' => $activeSchoolId,
                'assignments_count' => $existingAssignments->count(),
                'assignments' => $existingAssignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'class_id' => $assignment->class_id,
                        'subject_id' => $assignment->subject_id
                    ];
                })
            ]);

            // Delete all assignments for this teacher in the active school
            $deleted = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->delete();

            Log::info('All teacher assignments removed', [
                'teacher_id' => $teacher->id,
                'teacher_user_id' => $request->teacher_id,
                'school_id' => $activeSchoolId,
                'deleted_count' => $deleted
            ]);
            return redirect()->back()->with('success', 'All assignments removed for teacher successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing teacher assignments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to remove teacher assignments: ' . $e->getMessage()]);
        }
    }

    public function getTeacherAssignments(Request $request)
    {
        try {
            // Get active school ID
            $activeSchoolId = session('active_school_id');

            if (!$activeSchoolId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active school selected'
                ], 400);
            }

            $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
                ->forSchool($activeSchoolId)
                ->get()
                ->map(function ($assignment) {
                    return [
                        'teacher_id' => $assignment->teacher_id,
                        'teacher_user_id' => $assignment->teacher->user_id,
                        'teacher_name' => $assignment->teacher->user->name,
                        'teacher_class_id' => $assignment->teacher->class_id,
                        'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : null,
                        'class_id' => $assignment->class_id,
                        'class_name' => $assignment->class->name,
                        'subject_id' => $assignment->subject_id,
                        'subject_name' => $assignment->subject->name,
                        'subject_code' => $assignment->subject->code,
                        'school_id' => $assignment->school_id
                    ];
                });

            Log::info('Teacher assignments loaded', [
                'active_school_id' => $activeSchoolId,
                'assignments_count' => $teacherAssignments->count()
            ]);

            return response()->json([
                'success' => true,
                'teacherAssignments' => $teacherAssignments
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching teacher assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch teacher assignments: ' . $e->getMessage()
            ], 500);
        }
    }



    public function removeSubjectFromClass(Request $request)
    {
        Log::info('removeSubjectFromClass called', [
            'request_data' => $request->all(),
            'active_school_id' => session('active_school_id')
        ]);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        try {
            // Get active school ID for additional validation
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->with('error', 'No active school selected');
            }

            // Check if the assignment exists before deleting
            $existingAssignment = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('school_id', $activeSchoolId)
                ->first();

            Log::info('Subject-class assignment lookup result', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'school_id' => $activeSchoolId,
                'assignment_found' => $existingAssignment ? true : false,
                'assignment_id' => $existingAssignment ? $existingAssignment->id : null
            ]);

            if (!$existingAssignment) {
                return redirect()->back()->with('error', 'Subject-class assignment not found');
            }

            // Delete the subject-class assignment
            $deleted = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('school_id', $activeSchoolId)
                ->delete();

            Log::info('Subject-class assignment removed', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'school_id' => $activeSchoolId,
                'deleted_count' => $deleted
            ]);
            return redirect()->back()->with('success', 'Subject removed from class successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing subject from class', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to remove subject from class: ' . $e->getMessage()]);
        }
    }
}
