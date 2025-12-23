<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // لو العمود موجود بالفعل (بسبب migration أقدم) ما نعملش حاجة
        if (Schema::hasColumn('projects', 'geha_code')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->integer('geha_code')->nullable()->after('name');
            $table->index(['geha_code']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('projects', 'geha_code')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['geha_code']);
            $table->dropColumn('geha_code');
        });
    }
};
