<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_item_category_counters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->unique()
                ->constrained('work_item_categories')
                ->cascadeOnDelete();

            $table->unsignedInteger('last_number')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_item_category_counters');
    }
};
