<?php

namespace Modules\ClassesSections\App\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Subject;
use Modules\Schools\App\Models\School;

class ClassSubject extends Model
{
    use HasFactory, BelongsToAcademicYear;

    protected $table = 'class_subject';

    protected $fillable = [
        'class_id',
        'subject_id',
        'school_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['class_name', 'subject_name', 'school_name'];

    public function getClassNameAttribute()
    {
        return $this->class->name;
    }

    public function getSubjectNameAttribute()
    {
        return $this->subject->name;
    }

    public function getSchoolNameAttribute()
    {
        return $this->school->name;
    }

    /**
     * Get the class that owns the assignment.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the subject that owns the assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the school that owns the assignment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Scope to filter by school.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to filter by class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}
