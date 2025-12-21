<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqItem extends Model
{
    protected $table = 'boq_items';

    protected $fillable = [
        'boq_id',
        'work_item_id',
        'unit_id',
        'quantity',
        'unit_price',
        'total_price',
        'sort_order',
        'notes',
    ];

    protected static function booted(): void
    {
        static::saving(function (BoqItem $item) {
            $qty = (float) ($item->quantity ?? 0);
            $price = (float) ($item->unit_price ?? 0);
            $item->total_price = round($qty * $price, 2);
        });

        static::saved(function (BoqItem $item) {
            $item->boq?->recalculateTotals();
        });

        static::deleted(function (BoqItem $item) {
            $item->boq?->recalculateTotals();
        });
    }

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class, 'boq_id');
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class, 'work_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
