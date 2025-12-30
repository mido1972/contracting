<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_progress_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('claim_id'); // boq_progress_claims.id

            // Retention / Materials / Penalty / Other
            $table->string('code', 30);

            // percent / fixed
            $table->string('method', 10)->default('fixed');

            // value: (10.00) if percent, or (1500.00) if fixed
            $table->decimal('value', 14, 2)->default(0);

            // calculated amount stored (negative or positive حسب الاستخدام)
            $table->decimal('amount', 14, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['claim_id', 'code'], 'bpd_claim_code_idx');

            $table->foreign('claim_id')->references('id')->on('boq_progress_claims')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_progress_deductions');
    }
};
