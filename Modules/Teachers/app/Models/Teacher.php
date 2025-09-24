<?php

namespace Modules\Teachers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Subject;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\ClassSubjectTeacher;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'school_id',
        'cnic',
        'gender',
        'marital_status',
        'role_id',
        'dob',
        'salary',
        'date_of_joining',
        'experience_years',
        'class_id',
        'status',
    ];

    protected $appends = ['user_name', 'school_name', 'class_name'];

    protected $dates = ['deleted_at'];

    public function getUserNameAttribute()
    {
        return $this->user?->name;
    }

    public function getSchoolNameAttribute()
    {
        return $this->school->name;
    }

    public function getClassNameAttribute()
    {
        return $this->class->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subjectAssignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'teacher_id', 'subject_id')
            ->withPivot(['class_id', 'school_id'])
            ->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject_teacher', 'teacher_id', 'class_id')
            ->withPivot(['subject_id', 'school_id'])
            ->withTimestamps();
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    // Scope for school-specific teachers
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    // Get teachers with assignments for a specific school
    public static function getWithAssignmentsForSchool($schoolId)
    {
        return static::with(['subjects' => function ($query) use ($schoolId) {
            $query->wherePivot('school_id', $schoolId);
        }, 'classes' => function ($query) use ($schoolId) {
            $query->wherePivot('school_id', $schoolId);
        }])->forSchool($schoolId)->get();
    }

    // Assign subjects to teacher for a specific class and school
    public function assignSubjects($subjectIds, $classId, $schoolId)
    {
        $assignments = [];
        foreach ($subjectIds as $subjectId) {
            $assignments[$subjectId] = [
                'class_id' => $classId,
                'school_id' => $schoolId
            ];
        }

        return $this->subjects()->sync($assignments);
    }
}
