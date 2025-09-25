<?php

use Illuminate\Support\Facades\Route;
use Modules\Certificates\Http\Controllers\AchievementController;
use Modules\Certificates\Http\Controllers\CertificatesController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('certificates', CertificatesController::class)->names('certificates');
    Route::get('certificates/{certificate}/print/{type}', [CertificatesController::class, 'printCertificate'])->name('certificates.print');
    Route::resource('achievements', AchievementController::class)->names('achievements');
});
