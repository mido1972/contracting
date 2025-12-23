<?php

namespace App\Models;

use App\Models\Sql\Geha;
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function currencyCode(): string
    {
        return $this->branch?->currency_code
            ?? $this->company?->currency_code
            ?? 'SAR';
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

    public function boqItems(): HasMany
    {
        return $this->hasMany(BoqItem::class, 'boq_id', 'boq_id');
    }
}
