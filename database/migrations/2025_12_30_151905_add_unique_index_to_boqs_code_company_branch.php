<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            // ✅ Unique per (company, branch, code)
            $table->unique(
                ['company_id', 'branch_id', 'code'],
                'boqs_company_branch_code_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropUnique('boqs_company_branch_code_unique');
        });
    }
};
