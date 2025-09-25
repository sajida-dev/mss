<?php

namespace Modules\ResultsPromotions\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ClassesSections\App\Models\Subject;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamResult;


class ExamPaper extends Model
{
    use HasFactory, BelongsToAcademicYear;

    /**
     * The attributes that are mass assignable.
     */


    use SoftDeletes;

    protected $table = 'exam_papers';
    protected $fillable = [
        'exam_id',
        'paper_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'passing_marks'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }
}
