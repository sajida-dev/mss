<?php

namespace Modules\ResultsPromotions\app\Models;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;

class AcademicResult extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'total_marks',
        'obtained_marks',
        'overall_percentage',
        'cumulative_gpa',
        'final_grade',
        'promotion_status',
        'promotion_remarks',
        'is_verified',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_verified' => 'boolean',
        'overall_percentage' => 'float',
        'cumulative_gpa' => 'float',
        'total_marks' => 'float',
        'obtained_marks' => 'float',
    ];

    protected $appends = [
        'student_name',
        'academic_year_name',
        'class_name',
        'section_name',
    ];

    // ------------------------
    // Relationships
    // ------------------------

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ------------------------
    // Accessors
    // ------------------------

    public function getStudentNameAttribute()
    {
        return $this->student?->name ?? '';
    }

    public function getAcademicYearNameAttribute()
    {
        return $this->academicYear?->name ?? '';
    }

    public function getClassNameAttribute()
    {
        return $this->class?->name ?? '';
    }

    public function getSectionNameAttribute()
    {
        return $this->section?->name ?? '';
    }

    // ------------------------
    // Scopes
    // ------------------------

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForAcademicYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }
}
