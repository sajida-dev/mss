<?php

namespace Modules\PapersQuestions\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'paper_id',
        'text',
        'type',
        'options',
        'answer',
        'marks',
        'question_number',
        'section',
    ];

    protected $casts = [
        'options' => 'array',
        'marks' => 'integer',
        'question_number' => 'integer',
    ];

    // Question types for traditional exam papers
    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_TRUE_FALSE = 'true_false';
    const TYPE_SHORT_ANSWER = 'short_answer';
    const TYPE_LONG_ANSWER = 'long_answer';
    const TYPE_ESSAY = 'essay';
    const TYPE_NUMERICAL = 'numerical';

    // Question sections
    const SECTION_OBJECTIVE = 'objective';
    const SECTION_SHORT_QUESTIONS = 'short_questions';
    const SECTION_LONG_QUESTIONS = 'long_questions';
    const SECTION_ESSAY = 'essay';

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public static function getQuestionTypes(): array
    {
        return [
            self::TYPE_MULTIPLE_CHOICE => 'Multiple Choice',
            self::TYPE_TRUE_FALSE => 'True/False',
            self::TYPE_SHORT_ANSWER => 'Short Answer',
            self::TYPE_LONG_ANSWER => 'Long Answer',
            self::TYPE_ESSAY => 'Essay',
            self::TYPE_NUMERICAL => 'Numerical',
        ];
    }

    public static function getQuestionSections(): array
    {
        return [
            self::SECTION_OBJECTIVE => 'Objective Questions',
            self::SECTION_SHORT_QUESTIONS => 'Short Questions',
            self::SECTION_LONG_QUESTIONS => 'Long Questions',
            self::SECTION_ESSAY => 'Essay Questions',
        ];
    }
}
