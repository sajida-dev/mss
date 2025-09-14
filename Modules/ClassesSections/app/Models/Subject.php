<?php

namespace Modules\ClassesSections\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Teachers\Models\Teacher;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\ClassSubjectTeacher;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    protected $dates = ['deleted_at'];

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject', 'subject_id', 'class_id')
            ->withPivot('school_id')
            ->withTimestamps();
    }

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'class_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'class_subject', 'subject_id', 'school_id');
    }

    // Scope for school-specific subjects
    public function scopeForSchool($query, $schoolId)
    {
        return $query->whereHas('classes', function ($q) use ($schoolId) {
            $q->wherePivot('school_id', $schoolId);
        });
    }

    // Get subjects assigned to a specific class in a school
    public static function getAssignedToClass($classId, $schoolId)
    {
        return static::whereHas('classes', function ($query) use ($classId, $schoolId) {
            $query->where('class_subject.class_id', $classId)
                ->where('class_subject.school_id', $schoolId);
        })->get();
    }

    // Get subjects with teachers for a specific class in a school
    public static function getWithTeachersForClass($classId, $schoolId)
    {
        return static::with(['teachers' => function ($query) use ($classId, $schoolId) {
            $query->wherePivot('class_id', $classId)
                ->wherePivot('school_id', $schoolId);
        }])->whereHas('classes', function ($q) use ($classId, $schoolId) {
            $q->where('class_subject.class_id', $classId)
                ->where('class_subject.school_id', $schoolId);
        })->get();
    }
}
