<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ لو العمود موجود بالفعل: نضيف فقط الـ FK/Index لو ناقصين
        if (! Schema::hasColumn('boqs', 'project_id')) {
            Schema::table('boqs', function (Blueprint $table) {
                $table->foreignId('project_id')
                    ->nullable()
                    ->after('branch_id');
            });
        }

        // FK + Index (نعملهم في بلوك مستقل عشان لو العمود موجود مسبقاً)
        Schema::table('boqs', function (Blueprint $table) {
            // ✅ Index (لو مش موجود)
            // ملاحظة: Laravel مفيهوش hasIndex رسمي، فلو عندك خطأ هنا هنشيله ونسيبه manual.
            // هنكتفي بإنشاء index باسم ثابت وهنعمل drop في down.
            $table->index(['company_id', 'branch_id', 'project_id'], 'boqs_context_idx');

            // ✅ FK: في Postgres لازم نعمله لو مش موجود.
            // Laravel برضه مفيهوش hasForeignKey، فلو طلع Duplicate FK هنبدّلها لخطة B.
            $table->foreign('project_id', 'boqs_project_id_fk')
                ->references('id')->on('projects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            // FK
            try {
                $table->dropForeign('boqs_project_id_fk');
            } catch (\Throwable $e) {
                // ignore
            }

            // Index
            try {
                $table->dropIndex('boqs_context_idx');
            } catch (\Throwable $e) {
                // ignore
            }

            // Column
            if (Schema::hasColumn('boqs', 'project_id')) {
                $table->dropColumn('project_id');
            }
        });
    }
};
