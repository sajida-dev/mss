<?php

namespace Modules\ResultsPromotions\app\Models;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;

class TermResult extends Model
{
    use SoftDeletes, \App\Traits\BelongsToAcademicYear;

    protected $guarded = [];

    protected $fillable = [
        'student_id',
        'exam_id',
        'exam_type_id',
        'academic_year_id',
        'total_subjects',
        'obtained_marks',
        'total_marks',
        'overall_percentage',
        'subjects_passed',
        'subjects_failed',
        'term_status',
        'grade_points',
        'grade',
        'remarks',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'overall_percentage' => 'float',
        'grade_points' => 'float',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'obtained_marks' => 'float',
        'total_marks' => 'float',
    ];

    protected $appends = [
        'student_name',
        'exam_name',
        'exam_type_name',
        'academic_year_name',
    ];

    // ----------------------------
    // Relationships
    // ----------------------------

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ----------------------------
    // Accessors
    // ----------------------------

    public function getStudentNameAttribute(): string
    {
        return $this->student?->name ?? '-';
    }

    public function getExamNameAttribute(): string
    {
        return $this->exam?->title ?? '-';
    }

    public function getExamTypeNameAttribute(): string
    {
        return $this->examType?->name ?? '-';
    }

    public function getAcademicYearNameAttribute(): string
    {
        return $this->academicYear?->name ?? '-';
    }

    // ----------------------------
    // Scopes
    // ----------------------------

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeForExamType($query, $examTypeId)
    {
        return $query->where('exam_type_id', $examTypeId);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeCompletedTerms($query, $studentId, $academicYearId)
    {
        return $query->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereHas('examType')
            ->verified();
    }

    // ----------------------------
    // Helper: Check if student completed all terms in year
    // ----------------------------

    public static function hasCompletedAllTerms($studentId, $academicYearId): bool
    {
        $expectedTermTypes = ExamType::count();
        $studentTermResults = static::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_verified', true)
            ->pluck('exam_type_id')
            ->unique()
            ->count();

        return $studentTermResults === $expectedTermTypes;
    }

    // ----------------------------
    // Helper: Get Final Term Result (if available)
    // ----------------------------

    public static function getFinalTermResult($studentId, $academicYearId)
    {
        return static::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereHas('examType', function ($q) {
                $q->where('is_final_term', true);
            })
            ->where('is_verified', true)
            ->first();
    }
}
