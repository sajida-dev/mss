<?php

use Illuminate\Support\Facades\Route;
use Modules\Schools\Http\Controllers\SchoolsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('schools', SchoolsController::class)->names('schools');
});
