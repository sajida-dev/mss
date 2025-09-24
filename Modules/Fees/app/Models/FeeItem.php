<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\Models\FeeInstallment;

class FeeItem extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'fee_id',
        'fee_installment_id',
        'type',
        'amount'
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }
    public function feeInstallment(): BelongsTo
    {
        return $this->belongsTo(FeeInstallment::class);
    }
}
