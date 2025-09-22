<?php

namespace Modules\ResultsPromotions\app\Models;

use App\Models\AcademicYear;
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
        'academic_year_id',
    ];

    protected $appends = [
        'exam_type_name',
        'exam_name',
        'class_name',
        'section_name',
        'academic_year_name',
        'subject_name',
        'student_name',
        'marked_by_name',
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
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function getExamTypeNameAttribute()
    {
        return $this->examPaper?->exam?->examType?->name ?? '-';
    }
    public function getClassNameAttribute()
    {
        return $this->examPaper?->exam?->class?->name ?? '-';
    }
    public function getSectionNameAttribute()
    {
        return $this->examPaper?->exam?->section?->name ?? '-';
    }
    public function getAcademicYearNameAttribute()
    {
        return $this->academicYear?->name ?? '-';
    }
    public function getSubjectNameAttribute()
    {
        return $this->examPaper?->subject?->name ?? '-';
    }
    public function getExamNameAttribute()
    {
        return $this->examPaper?->exam?->title ?? '-';
    }

    public function getStudentNameAttribute()
    {
        return $this->student?->name ?? '-';
    }
    public function getMarkedByNameAttribute()
    {
        return $this->markedBy?->name ?? '-';
    }
}
