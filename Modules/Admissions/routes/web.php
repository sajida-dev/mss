<?php

use Illuminate\Support\Facades\Route;
use Modules\Admissions\Http\Controllers\AdmissionsController;

Route::middleware(['web',  'auth', 'verified', 'set.active.school', 'team.permission'])->prefix('admissions')->name('admissions.')->group(function () {
    Route::get('/', [AdmissionsController::class, 'index'])->name('index');
    Route::get('/create', [AdmissionsController::class, 'create'])->name('create');
    Route::post('/', [AdmissionsController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AdmissionsController::class, 'edit'])->name('edit');
    Route::post('/{id}', [AdmissionsController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdmissionsController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/approve', [AdmissionsController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [AdmissionsController::class, 'reject'])->name('reject');
});
