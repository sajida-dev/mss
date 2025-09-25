<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
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

        if ($user && $user->hasRole('superadmin')) {
            setPermissionsTeamId(null);
        } else {
            setPermissionsTeamId(session('active_school_id'));
        }

        $exams = Exam::query()
            ->select([
                'id',
                'exam_type_id',
                'academic_year_id',
                'result_entry_deadline',
                'class_id',
                'section_id',
                'created_at'
            ])
            ->whereNotNull('result_entry_deadline')
            ->whereDate('result_entry_deadline', '>', now())
            ->orderBy('result_entry_deadline', 'asc')
            ->with([
                'examType:id,name',
                'academicYear:id,name'
            ])
            ->limit(100)
            ->get();

        $grouped = $exams->groupBy(function ($exam) {
            $type = $exam->examType?->name ?? 'Unknown Exam';
            $year = $exam->academicYear?->name ?? 'Unknown Year';
            $date = \Carbon\Carbon::parse($exam->result_entry_deadline)->format('Y-m-d');
            return "{$type}|{$year}|{$date}";
        });

        $upcomingExams = $grouped->map(function ($group) {
            $first = $group->first();

            return [
                'id' => $first->id,
                'exam_type_name' => $first->examType?->name ?? 'Unknown Exam',
                'academic_year_name' => $first->academicYear?->name ?? 'Unknown Year',
                'result_entry_deadline' => $first->result_entry_deadline,
                'classes' => $group->pluck('class_id')->unique()->values(),
                'is_new' => \Carbon\Carbon::parse($first->created_at)->gt(now()->subDays(2)),
            ];
        })->values();

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
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'activeSchool' => fn() => School::find(session('active_school_id')),
            'upcomingExams' => $upcomingExams,
            'isSuperAdmin' => $user ? $user->hasRole('superadmin') : false,
        ]);
    }
}
