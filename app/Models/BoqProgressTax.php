<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqProgressTax extends Model
{
    protected $table = 'boq_progress_taxes';

    protected $fillable = [
        'claim_id',
        'tax_code',
        'rate',
        'taxable_amount',
        'tax_amount',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(BoqProgressClaim::class, 'claim_id');
    }
}
