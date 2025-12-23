<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->nullable()->unique();
            $table->string('name', 255);

            // الحالة - خليها lowercase عشان consistent مع شاشاتك الحالية
            $table->string('status', 20)->default('active');

            $table->text('notes')->nullable();

            // ✅ ربط اختياري بمقايسة (مرحلة أولى)
            $table->foreignId('boq_id')
                ->nullable()
                ->constrained('boqs')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['boq_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
