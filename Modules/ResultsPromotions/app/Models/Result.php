<?php

namespace Modules\ResultsPromotions\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\Schools\App\Models\School;

class Result extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'paper_id',
        'class_id',
        'section_id',
        'school_id',
        'term', // 1st_term, 2nd_term, 3rd_term, final
        'obtained_marks',
        'total_marks',
        'remarks',
        'marked_by', // user_id who marked the result
    ];

    protected $casts = [
        'obtained_marks' => 'decimal:2',
        'total_marks' => 'decimal:2',
    ];

    // Term constants
    const TERM_1ST = '1st_term';
    const TERM_2ND = '2nd_term';
    const TERM_3RD = '3rd_term';
    const TERM_FINAL = 'final';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'marked_by');
    }

    public static function getTerms(): array
    {
        return [
            self::TERM_1ST => '1st Term',
            self::TERM_2ND => '2nd Term',
            self::TERM_3RD => '3rd Term',
            self::TERM_FINAL => 'Final Term',
        ];
    }

    public function getPercentageAttribute(): float
    {
        if ($this->total_marks > 0) {
            return round(($this->obtained_marks / $this->total_marks) * 100, 2);
        }
        return 0;
    }

    public function getGradeAttribute(): string
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        return 'F';
    }
}
