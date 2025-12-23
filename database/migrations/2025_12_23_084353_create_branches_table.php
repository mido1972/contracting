<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Identifier (Unique per company)
            $table->string('code', 50); // مثال: RUH, JED, CAI

            // Names
            $table->string('name_ar', 255);
            $table->string('name_en', 255)->nullable();

            // Override settings (optional)
            $table->string('currency_code', 3)->nullable();     // null => inherit company
            $table->string('currency_symbol', 5)->nullable();   // null => inherit company
            $table->string('locale', 5)->nullable();            // null => inherit company
            $table->string('timezone', 50)->nullable();         // null => inherit company

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes / Uniques
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'name_ar']);
            $table->index(['company_id', 'name_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
