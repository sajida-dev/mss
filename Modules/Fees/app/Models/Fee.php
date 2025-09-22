<?php

namespace Modules\Fees\App\Models;

use App\Traits\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Fees\App\Models\FeeItem;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Fees\Models\FeeInstallment;
use NumberToWords\NumberToWords;

class Fee extends Model
{
    use SoftDeletes, BelongsToAcademicYear;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'student_id',
        'class_id',
        'type',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'fine_amount',
        'fine_due_date',
        'voucher_number',
        'paid_voucher_image',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'voucher_number',
        'paid_voucher_image',

    ];

    protected $appends = ['amount_in_words', 'fine_amount_in_words', 'student_name', 'class_name'];


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
        return $this->amountToWords($this->attributes['fine_amount']);
    }

    function getStudentNameAttribute()
    {
        return $this->student->name;
    }

    function getClassNameAttribute()
    {
        return $this->class->name;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function feeItems(): HasMany
    {
        return $this->hasMany(FeeItem::class);
    }
    public function installments()
    {
        return $this->hasMany(FeeInstallment::class);
    }
}
