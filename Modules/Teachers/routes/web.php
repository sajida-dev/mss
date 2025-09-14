<?php

use Illuminate\Support\Facades\Route;
use Modules\Teachers\Http\Controllers\TeachersController;

Route::middleware(['auth', 'role:admin|superadmin', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('teachers', TeachersController::class)->names('teachers');
    Route::get('teachers/get-password/{id}', [TeachersController::class, 'getTeacherPassword']);
    Route::post('teachers/{id}/approve', [TeachersController::class, 'approveTeacher'])->name('teachers.approve');
});
