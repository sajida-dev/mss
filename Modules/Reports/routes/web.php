<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportsController;

Route::middleware(['auth', 'set.active.school', 'verified', 'team.permission'])->group(function () {
    Route::resource('reports', ReportsController::class)->names('reports');
});
