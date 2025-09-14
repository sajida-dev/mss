<?php

use Illuminate\Support\Facades\Route;
use Modules\Fees\Http\Controllers\FeeInstallmentController;
use Modules\Fees\Http\Controllers\FeesController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('fees', FeesController::class)->names('fees');
    Route::get('fees/classes/by-school', [FeesController::class, 'getClasses'])->name('fees.classes.by-school');
    Route::get('fees/students/by-class', [FeesController::class, 'getStudents'])->name('fees.students.by-class');

    Route::prefix('installments')->controller(FeeInstallmentController::class)->group(function () {
        Route::get('/{fee}/create', 'create')->name('installments.create');
        Route::post('/', 'store')->name('installments.store');
        Route::post('/{installment}/pay', 'markAsPaid')->name('installments.pay');
    });
    Route::post('/fees/{fee}/mark-as-paid', [FeesController::class, 'markAsPaid'])->name('fees.markAsPaid');
});
