<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            if (Schema::hasColumn('boqs', 'project_ref')) {
                $table->dropColumn('project_ref');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            if (! Schema::hasColumn('boqs', 'project_ref')) {
                $table->string('project_ref', 100)
                    ->nullable()
                    ->after('name');
            }
        });
    }
};
