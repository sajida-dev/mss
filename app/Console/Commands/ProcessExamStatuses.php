<?php

namespace App\Console\Commands;

use App\Services\ResultService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\app\Models\Exam;

class ProcessExamStatuses extends Command
{
    protected $signature = 'exams:process-statuses';
    protected $description = 'Update exam statuses and trigger finalizations';

    public function handle()
    {
        $today = Carbon::today();

        // 1. Mark exams as in_progress
        Exam::where('status', 'scheduled')
            ->whereDate('start_date', '<=', $today)
            ->update(['status' => 'in_progress']);

        // 2. Check for completion
        $exams = Exam::with(['examPapers.results', 'class', 'section', 'examType'])
            ->where('status', 'in_progress')
            ->get();

        foreach ($exams as $exam) {
            $studentsCount = Student::where('class_id', $exam->class_id)
                ->when($exam->section_id, fn($q) => $q->where('section_id', $exam->section_id))
                ->admitted()
                ->count();

            $allResultsEntered = true;

            foreach ($exam->examPapers as $paper) {
                if ($paper->results->count() < $studentsCount) {
                    $allResultsEntered = false;
                    break;
                }
            }

            if ($allResultsEntered) {
                DB::transaction(function () use ($exam) {
                    $exam->update(['status' => 'completed']);

                    // Call your logic to generate term results
                    app(ResultService::class)->finalizeTermResult($exam);
                });

                $this->info("Finalized exam: {$exam->title}");
            }
        }

        $this->info("Exam statuses processed successfully.");
    }
}
