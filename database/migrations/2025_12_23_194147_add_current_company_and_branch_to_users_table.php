<?php

// database/migrations/xxxx_add_current_company_and_branch_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_company_id')->nullable()->constrained('companies')->nullOnDelete()->after('id');
            $table->foreignId('current_branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('current_company_id');

            $table->index(['current_company_id', 'current_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['current_company_id', 'current_branch_id']);
            $table->dropConstrainedForeignId('current_branch_id');
            $table->dropConstrainedForeignId('current_company_id');
        });
    }
};

