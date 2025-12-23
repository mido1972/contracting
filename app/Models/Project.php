<?php

namespace App\Models;

use App\Models\Sql\Geha;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'code',
        'name',
        'geha_code',
        'status',
        'notes',
        'boq_id',
    ];

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
}
