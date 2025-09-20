<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\app\Models\AcademicResult;
use Modules\ResultsPromotions\app\Models\TermResult;

class CalculateAcademicResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-academic-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $students = Student::admitted()->get(); // adjust your query to filter school/class/year etc.

        foreach ($students as $student) {
            $terms = TermResult::where('student_id', $student->id)
                ->orderBy('exam_type_id')
                ->get();

            // Skip if not all 3 terms are calculated
            if ($terms->count() < 3) {
                continue;
            }

            $term1 = $terms->get(0);
            $term2 = $terms->get(1);
            $term3 = $terms->get(2);

            $overallPercentage = round((
                $term1->overall_percentage +
                $term2->overall_percentage +
                $term3->overall_percentage
            ) / 3, 2);

            $academicYear = $term1->academic_year; // assumed same for all 3
            $classId = $student->class_id;
            $sectionId = $student->section_id;

            // Determine final grade (you can use a helper)
            $finalGrade = $this->getGrade($overallPercentage);
            $promotionStatus = $overallPercentage >= 40 ? 'promoted' : 'detained';

            AcademicResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $academicYear,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                ],
                [
                    'term1_percentage' => $term1->overall_percentage,
                    'term2_percentage' => $term2->overall_percentage,
                    'term3_percentage' => $term3->overall_percentage,
                    'overall_percentage' => $overallPercentage,
                    'cumulative_gpa' => null, // calculate if you use GPA system
                    'final_grade' => $finalGrade,
                    'promotion_status' => $promotionStatus,
                    'promotion_remarks' => null, // optional
                ]
            );
        }
    }

    private function getGrade($percentage)
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }
}
