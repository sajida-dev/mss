<?php

namespace Modules\PapersQuestions\App\Models;

use App\Models\AcademicYear;
use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\ClassesSections\app\Models\Subject;
use Modules\Teachers\Models\Teacher;

class Paper extends Model
{
    use SoftDeletes, BelongsToAcademicYear;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'class_id',
        'section_id',
        'teacher_id',
        'school_id',
        'subject_id',
        'title',
        'published',
        'total_marks',
        'time_duration',
        'course_name',
        'course_code',
        'program',
        'semester',
        'session',
        'exam_date',
        'instructions',
    ];

    protected $appends = [
        'class_name',
        'academic_year_name',
        'teacher_name',
        'section_name',
        'subject_name',
    ];

    protected $casts = [
        'published' => 'boolean',
        'total_marks' => 'integer',
        'time_duration' => 'integer', // in minutes
        'exam_date' => 'datetime:d-m-Y',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    // Get questions grouped by section
    public function getQuestionsBySection()
    {
        return $this->questions()->orderBy('section')->orderBy('question_number')->get()->groupBy('section');
    }

    // Calculate total marks
    public function getTotalMarksAttribute()
    {
        return $this->questions()->sum('marks');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function getClassNameAttribute()
    {
        return $this->class ? $this->class->name : null;
    }

    public function getAcademicYearNameAttribute()
    {
        return $this->academicYear ? $this->academicYear->name : null;
    }

    public function getSectionNameAttribute()
    {
        return $this->section?->name;
    }

    public function getTeacherNameAttribute()
    {
        return $this->teacher?->user?->name;
    }
    public function getSubjectNameAttribute()
    {
        return $this->subject?->name;
    }
}
