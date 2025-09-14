<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use App\Observers\PermissionObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Permission::observe(PermissionObserver::class);
        // // Grant all permissions to super admin
        // Gate::before(function ($user, $ability) {
        //     return $user->hasRole('superadmin') ? true : null;
        // });
        // /** @var \App\Models\User|null $user */
        // $user = Auth::user();

        // // Share permissions globally with Inertia
        // Inertia::share([
        //     'auth' => fn() => [
        //         'user' => $user,
        //         'can' => Auth::check()
        //             ? $user->getAllPermissions()->pluck('name')->mapWithKeys(fn($p) => [$p => true])->toArray()
        //             : [],
        //     ],
        // ]);

    }
}
