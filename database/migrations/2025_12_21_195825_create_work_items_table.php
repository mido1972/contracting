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
        Schema::create('work_items', function (Blueprint $table) {
            $table->id();

            // تصنيف البند (خرسانة – تشطيبات – كهرباء ...)
            $table->foreignId('category_id')
                ->constrained('work_item_categories')
                ->restrictOnDelete();

            // بند رئيسي / فرعي (Hierarchical)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('work_items')
                ->nullOnDelete();

            // بيانات البند
            $table->string('code', 30)->unique();
            $table->string('name');

            // الوحدة والسعر (على البنود الفرعية فقط)
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('default_price', 14, 2)->nullable();

            // تفعيل / إيقاف
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};
