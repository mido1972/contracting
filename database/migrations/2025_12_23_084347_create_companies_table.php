<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Identifier
            $table->string('code', 50)->unique(); // مثال: KSA, EGY

            // Names
            $table->string('name_ar', 255);
            $table->string('name_en', 255)->nullable();

            // Localization
            $table->string('currency_code', 3)->default('SAR'); // ISO 4217
            $table->string('currency_symbol', 5)->default('SAR'); // SAR, EGP, $
            $table->string('locale', 5)->default('ar'); // ar / en
            $table->string('timezone', 50)->default('Asia/Riyadh');

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
