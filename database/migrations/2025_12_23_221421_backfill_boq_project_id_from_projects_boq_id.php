<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // نقل العلاقة القديمة: projects.boq_id -> boqs.project_id
        DB::statement("
            update boqs
            set project_id = projects.id
            from projects
            where projects.boq_id = boqs.id
              and boqs.project_id is null
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // رجّع فقط اللي تم ملؤه عبر العلاقة القديمة
        DB::statement("
            update boqs
            set project_id = null
            from projects
            where projects.boq_id = boqs.id
        ");
    }
};
