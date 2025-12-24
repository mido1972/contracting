<?php

namespace App\Models;

use App\Models\Sql\Geha;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        // 'boq_id', // ❌ هنوقف الاعتماد عليه (هنحذفه لاحقًا من DB)
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
     * ✅ Project has many BOQs (preferred)
     */
    public function boqs(): HasMany
    {
        return $this->hasMany(Boq::class, 'project_id');
    }

    /**
     * ✅ Project has many BOQ items through BOQs
     * This enables showing all BOQ items inside the Project page (read-only).
     */
    public function boqItemsViaBoqs(): HasManyThrough
    {
        return $this->hasManyThrough(
            BoqItem::class, // final
            Boq::class,     // through
            'project_id',   // FK on boqs -> projects.id
            'boq_id',       // FK on boq_items -> boqs.id
            'id',           // local key on projects
            'id'            // local key on boqs
        );
    }

    /**
     * Read-only relation to ERP Geha_Data (SQL Server).
     */
    public function geha(): BelongsTo
    {
        return $this->belongsTo(Geha::class, 'geha_code', 'Geha_Code');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function currencyCode(): string
    {
        return $this->branch?->currencyCode()
            ?: $this->company?->currencyCode()
            ?: config('app.currency_default', 'SAR');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
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
