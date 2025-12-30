<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
