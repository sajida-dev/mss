<?php

namespace Modules\Certificates\App\Models;

use App\Models\AcademicYear;
use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\Schools\App\Models\School;

class Certificate extends Model
{
    use SoftDeletes, BelongsToAcademicYear;

    protected $fillable = [
        'achievement_id',
        'school_id',
        'student_id',
        'type',
        'issued_at',
        'details',
        'academic_year_id',
    ];

    protected $dates = ['issued_at'];
    protected $appends = ['school_name', 'academic_year_name', 'student_name', 'achievement_title'];

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : null;
    }

    public function getAcademicYearNameAttribute()
    {
        return $this->academicYear ? $this->academicYear->name : null;
    }

    public function getStudentNameAttribute()
    {
        return $this->student ? $this->student->name : null;
    }
    public function getAchievementTitleAttribute()
    {
        return $this->achievement ? $this->achievement->title : null;
    }
}
