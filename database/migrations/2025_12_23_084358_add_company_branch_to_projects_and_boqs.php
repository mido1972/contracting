<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->after('company_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('boqs', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->after('company_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['branch_id', 'company_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['branch_id', 'company_id']);
        });
    }
};
