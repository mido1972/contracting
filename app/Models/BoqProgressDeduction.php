<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqProgressDeduction extends Model
{
    protected $table = 'boq_progress_deductions';

    protected $fillable = [
        'claim_id',
        'code',
        'method',
        'value',
        'amount',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(BoqProgressClaim::class, 'claim_id');
    }
}
