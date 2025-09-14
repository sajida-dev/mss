<?php

namespace Modules\ClassesSections\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSubject extends Model
{
    use HasFactory;

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

    /**
     * Get the class that owns the assignment.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(\Modules\ClassesSections\App\Models\ClassModel::class, 'class_id');
    }

    /**
     * Get the subject that owns the assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(\Modules\ClassesSections\App\Models\Subject::class, 'subject_id');
    }

    /**
     * Get the school that owns the assignment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(\Modules\Schools\App\Models\School::class, 'school_id');
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
