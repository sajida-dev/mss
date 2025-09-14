<?php

use Illuminate\Support\Facades\Route;
use Modules\PapersQuestions\Http\Controllers\PapersQuestionsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('papersquestions', PapersQuestionsController::class)->names('papersquestions');
});
