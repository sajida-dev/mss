<?php

use App\Console\Commands\CalculateAcademicResults;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ProcessExamStatuses;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ProcessExamStatuses::class)->everyMinute();
// Schedule::command(CalculateAcademicResults::class)->everyMinute();
