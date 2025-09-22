<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\Schools\App\Models\School;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $exams = Exam::with('examType')
            ->whereNotNull('result_entry_deadline')
            ->whereDate('result_entry_deadline', '>', now())
            ->orderBy('result_entry_deadline', 'asc')
            ->get(['id', 'exam_type_id', 'academic_year_id', 'result_entry_deadline', 'class_id', 'section_id']);

        // group by exam type name + deadline date
        $grouped = $exams->groupBy(function ($exam) {
            $examTypeName = $exam->examType?->name ?? 'Unknown Exam Type';
            $date = \Carbon\Carbon::parse($exam->result_entry_deadline)->format('Y-m-d');
            return $examTypeName . '|' . $date;
        });

        // pick first exam per group
        $upcomingExams = $grouped->map(fn($group) => $group->first())->values();

        // optionally, add all classes sharing this exam group
        $upcomingExams = $grouped->map(function ($group) {
            $first = $group->first();
            $examTypeName = $first->examType?->name ?? 'Unknown Exam Type';
            $date = \Carbon\Carbon::parse($first->result_entry_deadline)->format('Y-m-d');

            $classes = $group->pluck('class_id')->unique()->values();
            $first->classes = $classes;
            $first->exam_type_name = $examTypeName;
            $first->date = $date;
            return $first;
        })->values();


        if ($user && $user->hasRole('superadmin')) {
            setPermissionsTeamId(null);
        } else {
            // Ensure team context is set before fetching roles/permissions
            setPermissionsTeamId(session('active_school_id'));
        }
        return array_merge(parent::share($request), [
            'name' => config('app.name'),

            'auth' => [
                'user' => $user ? $user->load('roles') : null,
                'roles' => $user ? $user->getRoleNames() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'can' => $user ? $user->permissionMap() : [],

            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'activeSchool' => fn() => School::find(session('active_school_id')),
            'upcomingExams' => $upcomingExams,
            'isSuperAdmin' => $user ? $user->hasRole('superadmin') : false,
        ]);
    }
}
