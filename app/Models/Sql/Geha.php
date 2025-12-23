<?php

namespace App\Models\Sql;

use Illuminate\Database\Eloquent\Model;

class Geha extends Model
{
    protected $connection = 'erp_sqlsrv';
    protected $table = 'dbo.Geha_Data';
    protected $primaryKey = 'Geha_Code';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}
