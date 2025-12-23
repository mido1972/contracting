<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boq extends Model
{
    protected $table = 'boqs';

    protected $fillable = [
        'company_id',
        'branch_id',
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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BoqItem::class, 'boq_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve currency with fallback:
     * BOQ.branch → BOQ.company → app default
     */
    public function currencyCode(): string
    {
        return $this->branch?->currencyCode()
            ?: $this->company?->currencyCode()
            ?: config('app.currency_default', 'SAR');
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculate BOQ total from DB (SUM of boq_items.total_price).
     * Must be called AFTER item is saved/deleted.
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

    /*
    |--------------------------------------------------------------------------
    | Scopes (Multi-tenant ready)
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }
}
