<?php

use Illuminate\Support\Facades\Route;
use Modules\ResultsPromotions\Http\Controllers\ResultsPromotionsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('resultspromotions', ResultsPromotionsController::class)->names('resultspromotions');
});
