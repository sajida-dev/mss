<?php

namespace Modules\ResultsPromotions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;

class TermResult extends Model
{
    protected $guarded = [];
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'exam_id',
        'exam_type_id',
        'academic_year',
        'total_subjects',
        'total_marks_obtained',
        'total_maximum_marks',
        'overall_percentage',
        'subjects_passed',
        'subjects_failed',
        'term_status',
        'grade_points',
        'grade',
        'remarks',
        'is_verified',
        'verified_by',
        'verified_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }
}
