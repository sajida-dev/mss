<?php

namespace Modules\ResultsPromotions\app\Models;

use App\Models\AcademicYear;
use App\Models\User;
use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;

class Exam extends Model
{
    protected $guarded = [];
    use SoftDeletes, BelongsToAcademicYear;

    // ------------------------
    // Fillable
    // ------------------------
    protected $fillable = [
        'school_id',
        'title',
        'exam_type_id',
        'class_id',
        'section_id',
        'start_date',
        'end_date',
        'result_entry_deadline',
        'status',
        'instructions',
        'academic_year_id',
        'created_by',
        'updated_by'
    ];
    // ------------------------
    // Casts
    // ------------------------
    protected $casts = [
        'result_entry_deadline' => 'datetime:Y-m-d',
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
        'academic_year_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // ------------------------
    // Appends  
    // ------------------------

    protected $appends = [
        'class_name',
        'section_name',
        'academic_year_name',
        'school_name',
        'exam_type_name',
        'created_by_name',
        'updated_by_name',
    ];
    // ------------------------
    // Relationships
    // ------------------------
    public function examPapers()
    {
        return $this->hasMany(ExamPaper::class);
    }
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }
    public function papers()
    {
        return $this->hasMany(ExamPaper::class);
    }
    public function termResults()
    {
        return $this->hasMany(TermResult::class);
    }
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    // ------------------------
    // Accessors
    // ------------------------
    public function getClassNameAttribute()
    {
        return $this->class ? $this->class->name : null;
    }
    public function getSectionNameAttribute()
    {
        return $this->section ? $this->section->name : null;
    }
    public function getAcademicYearNameAttribute()
    {
        return $this->academicYear ? $this->academicYear->name : null;
    }
    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : null;
    }
    public function getExamTypeNameAttribute()
    {
        return $this->examType ? $this->examType->name : null;
    }

    public function getCreatedByNameAttribute()
    {
        return $this->createdBy?->name ?? null;
    }
    public function getUpdatedByNameAttribute()
    {
        return $this->updatedBy?->name ?? null;
    }
}
