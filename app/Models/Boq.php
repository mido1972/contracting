<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Boq extends Model
{
    protected $table = 'boqs';

    protected $fillable = [
        'company_id',
        'branch_id',
        'project_id',   // ✅ Preferred FK
        'code',
        'name',
        'project_ref',  // ✅ optional for legacy/printing
        'status',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot (Auto code generation)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (self $boq) {
            // ✅ Fill tenant context if missing (common in Filament forms)
            $user = Auth::user();

            if ($user) {
                if (! filled($boq->branch_id) && filled($user->current_branch_id)) {
                    $boq->branch_id = (int) $user->current_branch_id;
                }

                if (! filled($boq->company_id) && filled($user->current_company_id)) {
                    $boq->company_id = (int) $user->current_company_id;
                }
            }

            // ✅ Generate code if missing (prevents NOT NULL violation)
            if (! filled($boq->code)) {
                $boq->code = self::nextCode(
                    companyId: $boq->company_id,
                    branchId: $boq->branch_id,
                );
            }

            // ✅ Keep project_ref synced if you still rely on it (optional)
            if (! filled($boq->project_ref) && filled($boq->project_id)) {
                $boq->project_ref = (string) $boq->project_id;
            }
        });
    }

    /**
     * Generate next sequential numeric code (per company/branch scope).
     * ✅ Safe for concurrency (PostgreSQL table lock)
     * ✅ Ignores non-numeric codes like "CIV-PRJ-MKK-001-B1"
     * ✅ Returns 6-digit padded string
     */
    public static function nextCode(?int $companyId, ?int $branchId): string
    {
        return DB::transaction(function () use ($companyId, $branchId) {

            // ✅ Prevent duplicates on concurrent creates (PostgreSQL)
            DB::statement('LOCK TABLE boqs IN SHARE ROW EXCLUSIVE MODE');

            $max = DB::table('boqs')
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                // ✅ take MAX only for numeric-only codes
                ->selectRaw("MAX(CASE WHEN code ~ '^[0-9]+$' THEN code::bigint END) as m")
                ->value('m');

            $next = ((int) $max) + 1;

            // ✅ 6 digits serial (000001)
            return str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }

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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BoqItem::class, 'boq_id');
    }

    /**
     * ✅ Progress Claims (المستخلصات) المرتبطة بالمقايسة
     */
    public function progressClaims(): HasMany
    {
        return $this->hasMany(BoqProgressClaim::class, 'boq_id');
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
    | Business Logic
    |--------------------------------------------------------------------------
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
     * Filter BOQs by authenticated user's current context.
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
