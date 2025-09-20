<?php

namespace Modules\ResultsPromotions\app\Models;

use App\Models\User;
use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\ResultsPromotions\Models\ExamPaper;


class ExamResult extends Model
{
    protected $guarded = [];
    use SoftDeletes, BelongsToAcademicYear;

    protected $fillable = [
        'exam_paper_id',
        'student_id',
        'obtained_marks',
        'total_marks',
        'percentage',
        'status',
        'promotion_status',
        'remarks',
        'marked_by',
    ];

    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function images()
    {
        return $this->hasMany(ExamResultImage::class);
    }
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
