<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();

            // الربط بالمقايسة
            $table->foreignId('boq_id')
                ->constrained('boqs')
                ->cascadeOnDelete();

            // الربط ببند الأعمال
            $table->foreignId('work_item_id')
                ->constrained('work_items')
                ->restrictOnDelete();

            // الوحدة (نسمح بالتغيير عن الافتراضي)
            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            // الكمية
            $table->decimal('quantity', 18, 3)->default(0);

            // سعر الوحدة وقت الدراسة
            $table->decimal('unit_price', 18, 2)->default(0);

            // الإجمالي = quantity * unit_price
            $table->decimal('total_price', 18, 2)->default(0);

            // ترتيب العرض داخل المقايسة
            $table->integer('sort_order')->default(0);

            // ملاحظات اختيارية على البند
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['boq_id']);
            $table->index(['work_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
