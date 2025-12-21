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

    public function items(): HasMany
    {
        return $this->hasMany(BoqItem::class, 'boq_id');
    }

    public function recalculateTotals(): void
    {
        $total = $this->items()->sum('total_price');

        $this->updateQuietly([
            'total_amount' => $total,
        ]);
    }
}
