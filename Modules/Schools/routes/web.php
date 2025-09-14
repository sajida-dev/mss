<?php

use Illuminate\Support\Facades\Route;
use Modules\Schools\Http\Controllers\SchoolsController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('schools', SchoolsController::class)->names('schools')->except(['update']);
    Route::post('schools/{id}', [SchoolsController::class, 'update'])->name('schools.update');
});
