<?php

namespace Modules\Fees\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\Fees\App\Models\Fee;

// use Modules\Fees\Database\Factories\FeeInstallmentFactory;

class FeeInstallment extends Model
{
    use HasFactory;
    use SoftDeletes;



    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fee_id',
        'student_id',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'voucher_number',
        'paid_voucher_image',
    ];

    protected $casts = [
        'due_date' => 'datetime:d-m-Y',
        'paid_at' => 'datetime:d-m-Y',
    ];

    protected $appends = ['student_name'];

    public function getStudentNameAttribute()
    {
        return $this->student->name;
    }

    protected $dates = ['due_date', 'paid_at'];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
