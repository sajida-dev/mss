<?php

namespace Modules\ClassesSections\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Admissions\Models\StudentEnrollment;
use Modules\ClassesSections\App\Models\Section;
use Modules\ClassesSections\App\Models\Subject;
use Modules\ClassesSections\App\Models\ClassSchool;
use Modules\ClassesSections\app\Models\ClassSubject;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\ClassSubjectTeacher;
use Modules\Teachers\Models\Teacher;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $fillable = ['name'];

    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function classSchools()
    {
        return $this->hasMany(ClassSchool::class, 'class_id');
    }

    public function sections()
    {
        return $this->belongsToMany(
            Section::class,
            'class_school_sections',
            'class_school_id',
            'section_id'
        )->wherePivotIn('class_school_id', function ($query) {
            $query->select('id')
                ->from('class_schools')
                ->where('class_id', $this->id);
        });
    }

    public function schools()
    {
        return $this->belongsToMany(
            School::class,
            'class_schools',
            'class_id',
            'school_id'
        );
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withPivot('school_id')
            ->withTimestamps();
    }

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'class_id');
    }

    public function classTeacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function scopeForClassIncharge($query, $userId)
    {
        return $query->whereHas('teachers', function ($q) use ($userId) {
            $q->where('teachers.user_id', $userId);
        });
    }

    // Scope for school-specific classes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->whereHas('schools', function ($q) use ($schoolId) {
            $q->where('schools.id', $schoolId);
        });
    }

    // Get classes with subjects for a specific school
    public static function getWithSubjectsForSchool($schoolId)
    {
        return static::with(['subjects' => function ($query) use ($schoolId) {
            $query->wherePivot('school_id', $schoolId);
        }])->forSchool($schoolId)->get();
    }

    // Get classes with teachers for a specific school
    public static function getWithTeachersForSchool($schoolId)
    {
        return static::with(['teachers' => function ($query) use ($schoolId) {
            $query->wherePivot('school_id', $schoolId);
        }])->forSchool($schoolId)->get();
    }

    // Assign subjects to class for a specific school
    public function assignSubjects($subjectIds, $schoolId)
    {
        $assignments = [];
        foreach ($subjectIds as $subjectId) {
            $assignments[$subjectId] = ['school_id' => $schoolId];
        }

        return $this->subjects()->sync($assignments);
    }
}
