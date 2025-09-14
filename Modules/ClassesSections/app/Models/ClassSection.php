<?php

namespace Modules\ClassesSections\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\app\Models\ClassSchool;
use Modules\ClassesSections\app\Models\SchoolClass;
use Modules\ClassesSections\app\Models\Section;

class ClassSection extends Model
{
    use SoftDeletes;
    protected $table = "class_school_section";

    protected $fillable = [
        'class_id',
        'section_id',
    ];

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
