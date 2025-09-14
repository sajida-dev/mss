<?php

use Illuminate\Support\Facades\Route;
use Modules\Students\Http\Controllers\StudentsController;

Route::middleware(['auth', 'verified', 'set.active.school', 'team.permission'])->group(function () {
    Route::resource('students', StudentsController::class)->names('students')->except(['update']);
    Route::post('students/{id}', [StudentsController::class, 'update'])->name('students.update');
});
