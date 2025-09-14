<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    // Main attendance routes
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // CRUD routes for individual attendance records
    Route::get('attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

    // Attendance report routes
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('attendance/statistics', [AttendanceController::class, 'statistics'])->name('attendance.statistics');
});
