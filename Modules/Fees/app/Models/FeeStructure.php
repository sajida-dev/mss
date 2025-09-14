<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'class',
        'section',
        'type',
        'amount',
        'installments_count',
        'installment_amounts'
    ];

    protected $casts = [
        'installment_amounts' => 'array',
    ];
}
