<?php

namespace Modules\ResultsPromotions\app\Models;

use App\Models\AcademicYear;
use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;

class Exam extends Model
{
    protected $guarded = [];
    use SoftDeletes, BelongsToAcademicYear;

    protected $fillable = [
        'school_id',
        'title',
        'exam_type_id',
        'class_id',
        'section_id',
        'academic_year',
        'start_date',
        'end_date',
        'result_entry_deadline',
        'status',
        'instructions',
        'created_by',
        'updated_by'
    ];
    public function examPapers()
    {
        return $this->hasMany(ExamPaper::class);
    }


    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }
    public function papers()
    {
        return $this->hasMany(ExamPaper::class);
    }
    public function termResults()
    {
        return $this->hasMany(TermResult::class);
    }
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
