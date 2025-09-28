<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportsController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->prefix('reports')->group(function () {
    // Route::resource('reports', ReportsController::class)->names('reports');

    Route::get('/students', [ReportsController::class, 'students'])->name('reports.students');
    Route::get('/attendance', [ReportsController::class, 'attendance'])->name('reports.attendance');
    Route::get('/fees', [ReportsController::class, 'fees'])->name('reports.fees');
    Route::get('/admissions', [ReportsController::class, 'admissions'])->name('reports.admissions');
    Route::get('/results', [ReportsController::class, 'results'])->name('reports.results');
    Route::get('/teachers', [ReportsController::class, 'teachers'])->name('reports.teachers');
});
