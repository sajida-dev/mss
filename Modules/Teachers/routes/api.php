<?php

use Illuminate\Support\Facades\Route;
use Modules\Teachers\Http\Controllers\TeachersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('teachers', TeachersController::class)->names('teachers');
});
