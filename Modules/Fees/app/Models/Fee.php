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
        'voucher_number',
        'paid_voucher_image',
    ];

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
