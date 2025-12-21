<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boq extends Model
{
    protected $table = 'boqs';

    protected $fillable = [
        'code',
        'name',
        'project_ref',
        'status',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BoqItem::class, 'boq_id');
    }

    /**
     * Recalculate BOQ total from DB (SUM of boq_items.total_price).
     * This must be called AFTER item is saved/deleted.
     */
    public function recalculateTotals(): void
    {
        // Sum directly from DB (no eager loading / no cached relations)
        $total = (float) $this->items()->sum('total_price');

        // Save without firing events to avoid loops
        $this->forceFill([
            'total_amount' => round($total, 2),
        ])->saveQuietly();
    }
}
