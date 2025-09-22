<?php

namespace Modules\ClassesSections\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\ClassesSections\app\Models\Section;

class ClassSection extends Model
{
    use SoftDeletes, BelongsToAcademicYear;
    protected $table = "class_school_section";

    protected $fillable = [
        'class_id',
        'section_id',
    ];

    protected $appends = ['class_name', 'section_name'];
    public function getClassNameAttribute()
    {
        return $this->class->class->name;
    }

    public function getSectionNameAttribute()
    {
        return $this->section->name;
    }
    protected $dates = ['deleted_at'];

    public function class()
    {
        return $this->belongsTo(ClassSchool::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
