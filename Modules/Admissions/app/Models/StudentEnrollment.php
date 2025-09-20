<?php

namespace Modules\Admissions\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admissions\Database\Factories\StudentEnrollmentFactory;

class StudentEnrollment extends Model
{
    use HasFactory, BelongsToAcademicYear;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'school_id',
        'class_id',
        'section_id',
        'academic_year',
        'admission_date',
        'status',
        'is_current',
    ];

    // protected static function newFactory(): StudentEnrollmentFactory
    // {
    //     // return StudentEnrollmentFactory::new();
    // }
}
