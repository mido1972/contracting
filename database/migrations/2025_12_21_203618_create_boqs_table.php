<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boqs', function (Blueprint $table) {
            $table->id();

            // كود المقايسة (مثلاً: BOQ-0001 أو 2025-001)
            $table->string('code', 30)->unique();

            // اسم المقايسة / اسم الدراسة
            $table->string('name', 255);

            // وصف مختصر اختياري
            $table->text('notes')->nullable();

            // حالة المقايسة (دراسة/تم الإرسال/تمت الترسية/مؤرشفة)
            $table->string('status', 20)->default('DRAFT');
            // DRAFT | SUBMITTED | AWARDED | ARCHIVED

            // إجماليات سريعة (هنحسبها لاحقاً من البنود)
            $table->decimal('total_amount', 18, 2)->default(0);

            // مرجع اختياري لاسم/رقم المشروع أو المناقصة (نضيف جداول المشاريع بعدين)
            $table->string('project_ref', 100)->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['project_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boqs');
    }
};
