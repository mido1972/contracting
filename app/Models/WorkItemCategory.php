<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkItemCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'notes',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(WorkItem::class, 'category_id');
    }
}
