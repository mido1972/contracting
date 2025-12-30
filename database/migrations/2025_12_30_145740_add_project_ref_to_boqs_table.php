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
            // مرجع المشروع (اختياري – للتوافق/الطباعة)
            $table->string('project_ref')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropColumn('project_ref');
        });
    }
};
