<?php

namespace Modules\ClassesSections\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ClassesSections\App\Models\ClassModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\Models\StudentEnrollment;

class Section extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];



    protected $dates = ['deleted_at'];

    public function classSchools()
    {
        return $this->belongsToMany(
            ClassModel::class,
            'class_school_sections',
            'section_id',
            'class_school_id'
        );
    }

    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
