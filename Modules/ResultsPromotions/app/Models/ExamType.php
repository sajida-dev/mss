<?php

namespace Modules\ResultsPromotions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'is_final_term'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function termResults()
    {
        return $this->hasMany(TermResult::class);
    }
}
