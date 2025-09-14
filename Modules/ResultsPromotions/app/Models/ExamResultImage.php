<?php

namespace Modules\ResultsPromotions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;

class ExamResultImage extends Model
{
    protected $guarded = [];
    use SoftDeletes;

    protected $fillable = [
        'exam_result_id',
        'path',
        'created_by',
        'updated_by'
    ];
}
