<?php

namespace Modules\ClassesSections\App\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Schools\App\Models\School;

class ClassSchool extends Model
{
    use SoftDeletes, BelongsToAcademicYear;

    protected $table = 'class_schools';

    protected $fillable = [
        'class_id',
        'school_id',
        'academic_year_id',
    ];

    protected $appends = ['class_name', 'school_name'];

    public function getClassNameAttribute()
    {
        return $this->class->name;
    }

    public function getSchoolNameAttribute()
    {
        return $this->school->name;
    }

    protected $dates = ['deleted_at'];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function sections()
    {
        return $this->belongsToMany(
            Section::class,
            'class_school_sections',
            'class_school_id',
            'section_id'
        );
    }
}
