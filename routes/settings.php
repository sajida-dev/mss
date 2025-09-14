<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'set.active.school', 'team.permission'])->group(function () {
    // Redirect /settings to /settings/profile
    Route::redirect('settings', '/settings/profile');

    // Profile routes
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password routes
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    // Appearance tab
    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // Roles & Permissions tab
    Route::get('settings/roles-permissions', function () {
        return Inertia::render('settings/RolesPermissions');
    })->name('roles.settings');

    // User Management
    Route::get('settings/users', [UserManagementController::class, 'index'])->name('settings.users');
    Route::get('settings/add-user', [UserManagementController::class, 'addUserPage'])->name('settings.add-user');
    Route::post('settings/user-management/create', [UserManagementController::class, 'createUser'])->name('settings.user-management.create');
    Route::post('settings/user-management/assign-role', [UserManagementController::class, 'assignRole'])->name('settings.user-management.assign-role');
    Route::delete('settings/user-management/remove-role', [UserManagementController::class, 'removeRole'])->name('settings.user-management.remove-role');
    Route::get('settings/user-management/get-password/{id}', [UserManagementController::class, 'getUserPassword'])->name('settings.user-management.get-password');
    Route::post('settings/user-management/verify-password', [UserManagementController::class, 'verifyPassword'])->name('settings.user-management.verify-password');
    Route::post('settings/user-management/reset-password', [UserManagementController::class, 'resetUserPassword'])->name('settings.user-management.reset-password');
});
