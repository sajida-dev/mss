<?php

use Illuminate\Support\Facades\Route;
use Modules\PapersQuestions\Http\Controllers\PapersQuestionsController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('papersquestions', PapersQuestionsController::class)->names('papersquestions');

    // Route to get subjects by class
    Route::get('papersquestions/subjects-by-class/{classId}', [PapersQuestionsController::class, 'getSubjectsByClass'])
        ->name('papersquestions.subjects-by-class');

    // Route to toggle publish status
    Route::patch('papersquestions/{id}/toggle-publish', [PapersQuestionsController::class, 'togglePublish'])
        ->name('papersquestions.toggle-publish');
    // Route to print paper
    Route::get('papersquestions/{id}/print', [PapersQuestionsController::class, 'print'])
        ->name('papersquestions.print');
});
