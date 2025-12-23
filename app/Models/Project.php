<?php

namespace App\Models;

use App\Models\Sql\Geha;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'geha_code',
        'status',
        'notes',
        'boq_id',
    ];

    protected $casts = [
        // 'geha_code' => 'integer', // اختياري
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

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class, 'boq_id');
    }

    /**
     * Read-only relation to ERP Geha_Data (SQL Server).
     * Joins by geha_code -> Geha_Data.Geha_Code
     */
    public function geha(): BelongsTo
    {
        return $this->belongsTo(Geha::class, 'geha_code', 'Geha_Code');
    }

    /**
     * Read-only: BOQ Items filtered by this project's boq_id
     * (works even if there is no direct project_id on boq_items)
     */
    public function boqItems(): HasMany
    {
        return $this->hasMany(BoqItem::class, 'boq_id', 'boq_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve currency with fallback:
     * Project.branch → Project.company → app default
     */
    public function currencyCode(): string
    {
        return $this->branch?->currencyCode()
            ?: $this->company?->currencyCode()
            ?: config('app.currency_default', 'SAR');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Multi-tenant / Context aware)
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
     * Filter projects by the authenticated user's current context:
     * - current_branch_id (strongest)
     * - current_company_id (fallback)
     *
     * @param  Builder<Project>  $query
     */
    public function scopeForCurrentContext(Builder $query): Builder
    {
        $user = Auth::user();

        // CLI / Seeder / unauthenticated
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
