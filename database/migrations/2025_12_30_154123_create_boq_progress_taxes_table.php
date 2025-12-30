<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_progress_taxes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('claim_id'); // boq_progress_claims.id

            $table->string('tax_code', 20)->default('VAT');
            $table->decimal('rate', 5, 2)->default(15.00);     // 15.00%
            $table->decimal('taxable_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);

            $table->timestamps();

            $table->unique(['claim_id', 'tax_code'], 'bpt_unique_claim_tax');

            $table->foreign('claim_id')->references('id')->on('boq_progress_claims')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_progress_taxes');
    }
};
