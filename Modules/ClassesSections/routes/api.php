<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassesSections\Http\Controllers\ClassesSectionsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('classessections', ClassesSectionsController::class)->names('classessections');
});
