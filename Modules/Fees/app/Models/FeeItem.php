<?php

namespace Modules\Fees\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeItem extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'fee_id',
        'type',
        'amount'
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }
}
