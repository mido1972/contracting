<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqProgressItem extends Model
{
    protected $table = 'boq_progress_items';

    /**
     * ✅ دايمًا حمّل البند الأصلي (BOQ Item)
     * علشان اسم البند يظهر في الجداول/RelationManagers بدون مشاكل
     */
    protected $with = [
        'boqItem',
    ];

    protected $fillable = [
        'claim_id',
        'boq_item_id',
        'qty_previous',
        'qty_current',
        'qty_total',
        'unit_price',
        'amount_current',
        'amount_total',
        'notes',
    ];

    /**
     * ✅ Postgres + Eloquent: الديسمل بيرجع string
     * وده طبيعي، بس بنثبت الـ casts علشان تنسيق العرض والحسابات يبقوا ثابتين
     */
    protected $casts = [
        'qty_previous'   => 'decimal:3',
        'qty_current'    => 'decimal:3',
        'qty_total'      => 'decimal:3',
        'unit_price'     => 'decimal:2',
        'amount_current' => 'decimal:2',
        'amount_total'   => 'decimal:2',
    ];

    /**
     * ✅ قيم افتراضية تمنع nulls في الحسابات / العرض
     */
    protected $attributes = [
        'qty_previous'   => 0,
        'qty_current'    => 0,
        'qty_total'      => 0,
        'unit_price'     => 0,
        'amount_current' => 0,
        'amount_total'   => 0,
        'notes'          => null,
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(BoqProgressClaim::class, 'claim_id');
    }

    public function boqItem(): BelongsTo
    {
        return $this->belongsTo(BoqItem::class, 'boq_item_id');
    }
}
