<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $table = 'units';

    protected $fillable = [
        'code',
        'name',
        'notes',
    ];

    /**
     * Work items that use this unit.
     */
    public function workItems(): HasMany
    {
        return $this->hasMany(WorkItem::class, 'unit_id');
    }
}
