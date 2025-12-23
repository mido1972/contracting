<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Boq extends Model
{
    protected $table = 'boqs';

    protected $fillable = [
        'company_id',
        'branch_id',
        'project_id',   // ✅ الجديد (أفضل)
        'code',
        'name',
        'project_ref',  // ✅ مؤقت للتوافق/الطباعة (اختياري)
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

    /**
     * ✅ Preferred relation (FK)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
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
        $total = (float) $this->items()->sum('total_price');

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

    /**
     * Filter BOQs by the authenticated user's current context:
     * - current_branch_id (strongest)
     * - current_company_id (fallback)
     * If no auth user (CLI/Seeder), no filter is applied.
     *
     * @param  Builder<Boq>  $query
     */
    public function scopeForCurrentContext(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        if (filled($user->current_branch_id)) {
            return $query->where('branch_id', (int) $user->current_branch_id);
        }

        if (filled($user->current_company_id)) {
            return $query->where('company_id', (int) $user->current_company_id);
        }

        return $query;
    }
}
