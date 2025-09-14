<?php

use Illuminate\Support\Facades\Route;
use Modules\Certificates\Http\Controllers\CertificatesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('certificates', CertificatesController::class)->names('certificates');
});
