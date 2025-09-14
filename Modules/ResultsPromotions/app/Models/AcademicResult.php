<?php

namespace Modules\ResultsPromotions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;

class AcademicResult extends Model
{
    protected $guarded = [];
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'academic_year',
        'class_id',
        'section_id',
        'term1_percentage',
        'term2_percentage',
        'term3_percentage',
        'overall_percentage',
        'cumulative_gpa',
        'final_grade',
        'promotion_status',
        'promotion_remarks',
        'is_verified',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
