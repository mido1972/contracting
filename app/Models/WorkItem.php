<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkItem extends Model
{
    protected $table = 'work_items';

    protected $fillable = [
        'category_id',
        'parent_id',
        'code',
        'name',
        'unit_id',
        'default_price',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    /**
     * Category (classification)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkItemCategory::class, 'category_id');
    }

    /**
     * Parent item (for tree structure)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children items
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }

    /**
     * Measurement unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
