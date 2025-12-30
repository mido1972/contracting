<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BoqProgressClaim extends Model
{
    protected $table = 'boq_progress_claims';

    protected $fillable = [
        'company_id',
        'branch_id',
        'project_id',
        'boq_id',
        'claim_no',
        'claim_date',
        'status',
        'previous_claim_id',
        'notes',
        'total_a_cumulative',
        'total_b_previous',
        'total_c_retention',
        'total_d_deductions',
        'vat_amount',
        'net_payable',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'total_a_cumulative' => 'decimal:2',
        'total_b_previous' => 'decimal:2',
        'total_c_retention' => 'decimal:2',
        'total_d_deductions' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot (Auto claim_no + context fill)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (self $claim) {
            // 0) BOQ لازم يكون موجود
            if (! filled($claim->boq_id)) {
                throw new \RuntimeException('boq_id مطلوب لإنشاء مستخلص');
            }

            // 1) Fill context from authenticated user if missing
            if ($user = Auth::user()) {
                if (! filled($claim->branch_id) && filled($user->current_branch_id)) {
                    $claim->branch_id = (int) $user->current_branch_id;
                }

                if (! filled($claim->company_id) && filled($user->current_company_id)) {
                    $claim->company_id = (int) $user->current_company_id;
                }
            }

            // 2) Infer/ensure context from BOQ (company/branch/project)
            $boq = Boq::query()
                ->select(['id', 'company_id', 'branch_id', 'project_id'])
                ->find($claim->boq_id);

            if ($boq) {
                if (! filled($claim->company_id) && filled($boq->company_id)) {
                    $claim->company_id = (int) $boq->company_id;
                }

                if (! filled($claim->branch_id) && filled($boq->branch_id)) {
                    $claim->branch_id = (int) $boq->branch_id;
                }

                if (! filled($claim->project_id) && filled($boq->project_id)) {
                    $claim->project_id = (int) $boq->project_id;
                }
            }

            // 3) Default claim_date
            if (! filled($claim->claim_date)) {
                $claim->claim_date = now()->toDateString();
            }

            // 4) Auto claim_no (per company+branch+boq)
            if (! filled($claim->claim_no)) {
                $claim->claim_no = self::nextClaimNo(
                    companyId: filled($claim->company_id) ? (int) $claim->company_id : null,
                    branchId: filled($claim->branch_id) ? (int) $claim->branch_id : null,
                    boqId: (int) $claim->boq_id
                );
            }
        });
    }

    /**
     * Next claim number per (company_id, branch_id, boq_id)
     * Concurrency-safe on PostgreSQL via table lock.
     */
    public static function nextClaimNo(?int $companyId, ?int $branchId, int $boqId): int
    {
        return DB::transaction(function () use ($companyId, $branchId, $boqId) {
            DB::statement('LOCK TABLE boq_progress_claims IN SHARE ROW EXCLUSIVE MODE');

            $max = DB::table('boq_progress_claims')
                ->where('boq_id', $boqId)
                ->when(! is_null($companyId), fn ($q) => $q->where('company_id', $companyId))
                ->when(! is_null($branchId), fn ($q) => $q->where('branch_id', $branchId))
                ->max('claim_no');

            return ((int) $max) + 1;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function boq(): BelongsTo
    {
        return $this->belongsTo(Boq::class, 'boq_id');
    }

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

    public function previousClaim(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_claim_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BoqProgressItem::class, 'claim_id');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(BoqProgressDeduction::class, 'claim_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(BoqProgressTax::class, 'claim_id');
    }
}
