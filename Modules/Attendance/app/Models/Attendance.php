<?php

namespace Modules\Attendance\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\Teacher;

class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'school_id',
        'date',
        'status', // present, absent, late, half_day
        'remarks',
        'marked_by', // user_id who marked the attendance
        'teacher_id', // teacher_id who is responsible for this class
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Attendance statuses
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALF_DAY = 'half_day';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LATE => 'Late',
            self::STATUS_HALF_DAY => 'Half Day',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'green',
            self::STATUS_ABSENT => 'red',
            self::STATUS_LATE => 'yellow',
            self::STATUS_HALF_DAY => 'orange',
            default => 'gray',
        };
    }
}
