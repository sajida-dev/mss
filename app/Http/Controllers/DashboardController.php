<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Schools\App\Models\School;
use App\Models\User;
use Modules\Admissions\App\Models\Student;
use Modules\Teachers\Models\Teacher;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Fees\App\Models\Fee;
use Modules\PapersQuestions\App\Models\Paper;

use Illuminate\Support\Facades\DB;
use Modules\Teachers\Models\ClassSubjectTeacher;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            // dd($user->roles);
            if (!$user) {
                Log::error('Dashboard accessed without authenticated user');
                return redirect()->route('login');
            }

            $activeSchoolId = session('active_school_id');

            Log::info('Dashboard accessed', [
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('name'),
                'active_school_id' => $activeSchoolId
            ]);

            // Initialize basic data
            $dashboardData = [
                'user' => $user,
                'userRoles' => $user->roles->pluck('name'),
                'activeSchoolId' => $activeSchoolId,
                'schools' => [],
                'stats' => [],
                'recentData' => [],
                'errors' => []
            ];

            // Handle Super Admin (no school required)
            if ($user->hasRole('superadmin')) {
                $dashboardData = $this->getSuperAdminData($dashboardData);
            } elseif ($user->hasRole(['admin', 'principal', 'teacher'])) { // Handle other roles (school required)
                if (!$activeSchoolId) {
                    $dashboardData['errors'][] = 'No active school selected. Please select a school.';
                    Log::warning('No active school for user', ['user_id' => $user->id, 'roles' => $user->roles->pluck('name')]);
                    return Inertia::render('Dashboard', $dashboardData);
                }

                $school = School::find($activeSchoolId);
                if (!$school) {
                    $dashboardData['errors'][] = 'Selected school not found.';
                    Log::error('School not found', ['school_id' => $activeSchoolId, 'user_id' => $user->id]);
                    return Inertia::render('Dashboard', $dashboardData);
                }

                $dashboardData['activeSchool'] = $school;
                $dashboardData = $this->getRoleBasedData($dashboardData, $user, $school);
            } else {
                $dashboardData['errors'][] = 'No valid role assigned. Please contact administrator.';
                Log::warning('User has no valid role', ['user_id' => $user->id]);
            }

            return Inertia::render('Dashboard', $dashboardData);
        } catch (\Exception $e) {
            Log::error('Dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return Inertia::render('Dashboard', [
                'user' => Auth::user(),
                'userRoles' => Auth::user() ? Auth::user()->roles->pluck('name') : [],
                'errors' => ['An error occurred while loading dashboard data. Please try again.']
            ]);
        }
    }

    private function getSuperAdminData($dashboardData)
    {
        // Get all schools for super admin
        $schools = School::all();
        $dashboardData['schools'] = $schools;

        // Get real counts
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassModel::count();


        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $studentsThisMonth = Student::whereMonth('created_at', $currentMonth)->count();
        $studentsLastMonth = Student::whereMonth('created_at', $lastMonth)->count();
        $studentChange = $studentsLastMonth > 0 ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1) : 0;

        $teachersThisMonth = Teacher::whereMonth('created_at', $currentMonth)->count();
        $teachersLastMonth = Teacher::whereMonth('created_at', $lastMonth)->count();
        $teacherChange = $teachersLastMonth > 0 ? round((($teachersThisMonth - $teachersLastMonth) / $teachersLastMonth) * 100, 1) : 0;

        // System-wide statistics with real data
        $dashboardData['stats'] = [
            [
                'label' => 'Total Schools',
                'value' => $schools->count(),
                'icon' => 'school',
                'color' => 'blue',
                'link' => 'schools.index',
                'change' => $schools->count() > 0 ? 'Active schools' : 'No schools yet'
            ],
            [
                'label' => 'Total Students',
                'value' => $totalStudents,
                'icon' => 'users',
                'color' => 'green',
                'link' => 'students.index',
                'change' => $studentChange > 0 ? "+{$studentChange}% this month" : "{$studentChange}% this month"
            ],
            [
                'label' => 'Total Teachers',
                'value' => $totalTeachers,
                'icon' => 'user-tie',
                'color' => 'purple',
                'link' => 'teachers.index',
                'change' => $teacherChange > 0 ? "+{$teacherChange}% this month" : "{$teacherChange}% this month"
            ],
            [
                'label' => 'Total Classes',
                'value' => $totalClasses,
                'icon' => 'graduation-cap',
                'color' => 'orange',
                'link' => 'classes.index',
                'change' => $totalClasses > 0 ? 'Active classes' : 'No classes yet'
            ]
        ];

        // Add recent data with error handling
        try {
            $dashboardData['recentData'] = [
                'recentSchools' => School::latest()->take(5)->get(),
                'recentTeachers' => Teacher::with('user', 'school')->latest()->take(5)->get(),
                'recentStudents' => Student::with('school')->latest()->take(5)->get(),
                'recentFees' => Fee::with(['student.school'])->latest()->take(5)->get(),

            ];
        } catch (\Exception $e) {
            Log::error('Error loading recent data', ['error' => $e->getMessage()]);
            $dashboardData['recentData'] = [
                'recentSchools' => collect([]),
                'recentTeachers' => collect([]),
                'recentStudents' => collect([]),
                'recentFees' => collect([]),
            ];
        }

        return $dashboardData;
    }

    private function getRoleBasedData($dashboardData, $user, $school)
    {
        if ($user->hasRole('admin')) {
            $dashboardData = $this->getAdminData($dashboardData, $school);
        } elseif ($user->hasRole('principal')) {
            $dashboardData = $this->getPrincipalData($dashboardData, $school);
        } elseif ($user->hasRole('teacher')) {
            $dashboardData = $this->getTeacherData($dashboardData, $school, $user);
        }

        return $dashboardData;
    }

    private function getAdminData($dashboardData, $school)
    {
        $schoolId = $school->id;

        // Get real counts for this school
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $totalClasses = ClassModel::where('school_id', $schoolId)->count();
        $totalSections = Section::whereHas('classes', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->count();

        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $studentsThisMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $currentMonth)->count();
        $studentsLastMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $lastMonth)->count();
        $studentChange = $studentsLastMonth > 0 ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1) : 0;

        $teachersThisMonth = Teacher::where('school_id', $schoolId)->whereMonth('created_at', $currentMonth)->count();
        $teachersLastMonth = Teacher::where('school_id', $schoolId)->whereMonth('created_at', $lastMonth)->count();
        $teacherChange = $teachersLastMonth > 0 ? round((($teachersThisMonth - $teachersLastMonth) / $teachersLastMonth) * 100, 1) : 0;

        $dashboardData['stats'] = [
            [
                'label' => 'Total Students',
                'value' => $totalStudents,
                'icon' => 'users',
                'color' => 'blue',
                'link' => 'students.index',
                'change' => $studentChange > 0 ? "+{$studentChange}% this month" : "{$studentChange}% this month"
            ],
            [
                'label' => 'Total Teachers',
                'value' => $totalTeachers,
                'icon' => 'user-tie',
                'color' => 'green',
                'link' => 'teachers.index',
                'change' => $teacherChange > 0 ? "+{$teacherChange}% this month" : "{$teacherChange}% this month"
            ],
            [
                'label' => 'Total Classes',
                'value' => $totalClasses,
                'icon' => 'graduation-cap',
                'color' => 'purple',
                'link' => 'classes.index',
                'change' => $totalClasses > 0 ? 'Active classes' : 'No classes yet'
            ],
            [
                'label' => 'Total Sections',
                'value' => $totalSections,
                'icon' => 'layer-group',
                'color' => 'orange',
                'link' => 'sections.index',
                'change' => $totalSections > 0 ? 'Active sections' : 'No sections yet'
            ]
        ];

        // Add recent data for this school
        $dashboardData['recentData'] = [
            'recentStudents' => Student::where('school_id', $schoolId)->with('school')->latest()->take(5)->get(),
            'recentFees' => Fee::where('school_id', $schoolId)->with(['student', 'school'])->latest()->take(5)->get(),
            'pendingFees' => Fee::where('school_id', $schoolId)->where('status', 'unpaid')->count(),
            'overdueFees' => Fee::where('school_id', $schoolId)->where('due_date', '<', now())->where('status', 'unpaid')->count(),
        ];

        return $dashboardData;
    }

    private function getPrincipalData($dashboardData, $school)
    {
        $schoolId = $school->id;

        // Get real counts for this school
        $totalStudents = Student::where('school_id', $schoolId)->where('status', 'admitted')->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $totalApplicants = Student::where('school_id', $schoolId)->where('status', 'applicant')->count();
        $totalAdmitted = Student::where('school_id', $schoolId)->where('status', 'admitted')->count();
        $totalPapers = Paper::where('school_id', $schoolId)->count();

        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $studentsThisMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $currentMonth)->count();
        $studentsLastMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $lastMonth)->count();
        $studentChange = $studentsLastMonth > 0 ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1) : 0;

        $dashboardData['stats'] = [
            [
                'label' => 'Total Students',
                'value' => $totalStudents,
                'icon' => 'users',
                'color' => 'blue',
                'link' => 'students.index',
                'change' => $studentChange > 0 ? "+{$studentChange}% this month" : "{$studentChange}% this month"
            ],
            [
                'label' => 'Total Teachers',
                'value' => $totalTeachers,
                'icon' => 'user-tie',
                'color' => 'green',
                'link' => 'teachers.index',
                'change' => $totalTeachers > 0 ? 'Active teachers' : 'No teachers yet'
            ],
            [
                'label' => 'Pending Applicants',
                'value' => $totalApplicants,
                'icon' => 'user-plus',
                'color' => 'yellow',
                'link' => 'admissions.index',
                'change' => $totalApplicants > 0 ? 'Awaiting admission decision' : 'No pending applications'
            ],
            [
                'label' => 'Total Papers',
                'value' => $totalPapers,
                'icon' => 'trophy',
                'color' => 'orange',
                'link' => 'papersquestions.index',
                'change' => $totalPapers > 0 ? 'Created papers' : 'No papers yet'
            ]
        ];

        // Add recent data for this school
        $dashboardData['recentData'] = [
            'recentApplicants' => Student::where('school_id', $schoolId)->where('status', 'applicant')->with('school')->latest()->take(5)->get(),
            'recentStudents' => Student::where('school_id', $schoolId)->where('status', 'admitted')->with('school')->latest()->take(5)->get(),
            'recentPapers' => Paper::where('school_id', $schoolId)->latest()->take(5)->get(),
        ];

        return $dashboardData;
    }

    private function getTeacherData($dashboardData, $school, $user)
    {
        $schoolId = $school->id;

        // Find teacher record for this user and school
        $teacher = Teacher::where('user_id', $user->id)->where('school_id', $schoolId)->first();

        if (!$teacher) {
            $dashboardData['errors'][] = 'Teacher profile not found for this school.';
            return $dashboardData;
        }
        $teacherClass = $teacher->class;

        // Get real counts for this teacher
        $myStudents = Student::where('class_id', $teacher->class_id)->where('school_id', $schoolId)->count();

        $myClasses = ClassModel::whereHas('classSubjectTeachers', function ($query) use ($user, $schoolId) {
            $query->where('teacher_id', $user->id)
                ->where('school_id', $schoolId);
        })->count();

        $papersCreated = Paper::where('school_id', $schoolId)->where('teacher_id', $user->id)->count();
        $subjects = ClassSubjectTeacher::where('school_id', $schoolId)->where('teacher_id', $user->id)->count();

        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $papersThisMonth = Paper::where('school_id', $schoolId)->where('created_by', $user->id)->whereMonth('created_at', $currentMonth)->count();
        $papersLastMonth = Paper::where('school_id', $schoolId)->where('created_by', $user->id)->whereMonth('created_at', $lastMonth)->count();
        $paperChange = $papersLastMonth > 0 ? round((($papersThisMonth - $papersLastMonth) / $papersLastMonth) * 100, 1) : 0;

        $dashboardData['stats'] = [
            [
                'label' => 'My Students',
                'value' => $myStudents,
                'icon' => 'users',
                'color' => 'blue',
                'link' => 'students.index',
                'change' => $myStudents > 0 ? 'Current students' : 'No students assigned'
            ],
            [
                'label' => 'My Classes',
                'value' => $myClasses,
                'icon' => 'graduation-cap',
                'color' => 'green',
                'link' => 'classes.index',
                'change' => $myClasses > 0 ? 'Active classes' : 'No classes assigned'
            ],
            [
                'label' => 'Papers Created',
                'value' => $papersCreated,
                'icon' => 'file-alt',
                'color' => 'purple',
                'link' => 'papersquestions.index',
                'change' => $paperChange > 0 ? "+{$paperChange}% this month" : "{$paperChange}% this month"
            ],
            [
                'label' => 'Subjects Teaching',
                'value' => $subjects,
                'icon' => 'user-plus',
                'color' => 'orange',
                'link' => 'subjects.index',
                'change' => $subjects > 0 ? 'Subjects Teaching' : 'No subjects yet'
            ]
        ];

        // Add recent data for this teacher
        $dashboardData['recentData'] = [
            'myStudents' => Student::where('school_id', $schoolId)->where('class_teacher_id', $teacher->id)->take(5)->get(),
            'myPapers' => Paper::where('school_id', $schoolId)->where('created_by', $user->id)->latest()->take(5)->get(),
            'myAdmissions' => Student::where('school_id', $schoolId)->where('admitted_by', $user->id)->latest()->take(5)->get(),
        ];

        return $dashboardData;
    }
}
