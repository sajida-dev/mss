<?php

namespace Modules\Fees\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\App\Models\FeeItem;
use NumberToWords\NumberToWords;


// use Modules\Fees\Database\Factories\FeeInstallmentFactory;

class FeeInstallment extends Model
{
    use HasFactory;
    use SoftDeletes, BelongsToAcademicYear;



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
        'fine_amount',
        'fine_due_date',
        'voucher_number',
        'paid_voucher_image',
    ];

    protected $casts = [
        'due_date' => 'datetime:d-m-Y',
        'paid_at' => 'datetime:d-m-Y',
    ];

    protected $appends = ['student_name', 'amount_in_words', 'fine_amount_in_words'];
    function amountToWords($amount)
    {
        $numberToWords = new NumberToWords();

        $numberTransformer = $numberToWords->getNumberTransformer('en');

        $words = $numberTransformer->toWords($amount); // converts 35000 to "thirty-five thousand"
        return 'Rs. ' . ucfirst($words) . ' Only';
    }

    function getAmountInWordsAttribute()
    {
        return $this->amountToWords($this->attributes['amount']);
    }

    function getFineAmountInWordsAttribute()
    {
        return $this->amountToWords($this->attributes['amount'] + $this->attributes['fine_amount']);
    }

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
    public function feeItems()
    {
        return $this->hasMany(FeeItem::class);
    }
}
