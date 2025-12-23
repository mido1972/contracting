<?php

namespace App\Models;

use App\Models\Sql\Geha;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        // لو status ثابت كـ string تمام، سيبها
        // 'geha_code' => 'integer', // اختياري لو عندك type ثابت
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
    | Scopes (مفيدين جدًا لما نعمل Multi-tenant)
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
