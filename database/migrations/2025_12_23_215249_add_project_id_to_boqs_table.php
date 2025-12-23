<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->foreignId('project_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('projects')
                ->nullOnDelete();

            $table->index(['company_id', 'branch_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id', 'project_id']);
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
