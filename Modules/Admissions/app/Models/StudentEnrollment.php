<?php

namespace Modules\Admissions\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Schools\App\Models\School;

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

    protected $casts = [
        'admission_date' => 'datetime:d-m-Y',
    ];

    protected $appends = ['student_name', 'class_name', 'section_name', 'school_name'];

    public function getStudentNameAttribute()
    {
        return $this->student?->name;
    }

    public function getClassNameAttribute()
    {
        return $this->class?->name;
    }

    public function getSectionNameAttribute()
    {
        return $this->section?->name;
    }

    public function getSchoolNameAttribute()
    {
        return $this->school?->name;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
