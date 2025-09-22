<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ResultsPromotions\app\Models\Exam;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    // Scope for active academic year
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Optional: Get current academic year singleton
    public static function current()
    {
        return static::active()->first();
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'academic_year_id');
    }
}
