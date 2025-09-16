<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
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
        $upcomingExams = Exam::whereNotNull('result_entry_deadline')
            ->whereDate('result_entry_deadline', '>', now())
            ->orderBy('result_entry_deadline', 'asc')
            ->get(['id', 'title', 'result_entry_deadline', 'class_id', 'section_id']);
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
        ]);
    }
}
