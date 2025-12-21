<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

    protected $casts = [
        'quantity'    => 'decimal:3',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order'  => 'integer',
    ];

    protected static function booted(): void
    {
        // 1) حساب إجمالي السطر قبل الحفظ
        static::saving(function (BoqItem $item) {
            $qty = (float) ($item->quantity ?? 0);
            $price = (float) ($item->unit_price ?? 0);

            $item->total_price = round($qty * $price, 2);
        });

        // 2) بعد الحفظ: حدّث إجمالي المقايسة (بعد Commit لو فيه Transaction)
        static::saved(function (BoqItem $item) {
            DB::afterCommit(function () use ($item) {
                $item->boq?->recalculateTotals();
            });
        });

        // 3) بعد الحذف: مهم جدًا لأن recalculateTotals بيعمل SUM من DB
        static::deleted(function (BoqItem $item) {
            DB::afterCommit(function () use ($item) {
                $item->boq?->recalculateTotals();
            });
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
