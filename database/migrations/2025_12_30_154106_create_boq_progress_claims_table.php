<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_progress_claims', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();

            $table->unsignedBigInteger('boq_id'); // required

            // رقم المستخلص (مثلاً: 1,2,3...) - Unique per (company, branch, boq)
            $table->unsignedInteger('claim_no');

            $table->date('claim_date')->nullable();

            // Draft / Submitted / Approved / Paid / Rejected
            $table->string('status', 20)->default('DRAFT');

            // ربط بالمستخلص السابق (اختياري)
            $table->unsignedBigInteger('previous_claim_id')->nullable();

            $table->text('notes')->nullable();

            // Totals (Snapshot at header level) - optional but useful for performance
            $table->decimal('total_a_cumulative', 14, 2)->default(0); // A
            $table->decimal('total_b_previous', 14, 2)->default(0);   // B
            $table->decimal('total_c_retention', 14, 2)->default(0);  // C
            $table->decimal('total_d_deductions', 14, 2)->default(0); // D
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('net_payable', 14, 2)->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'branch_id', 'project_id'], 'bpc_context_idx');
            $table->index(['boq_id', 'claim_no'], 'bpc_boq_claimno_idx');
            $table->unique(['company_id', 'branch_id', 'boq_id', 'claim_no'], 'bpc_unique_claimno');

            // FKs
            $table->foreign('boq_id')->references('id')->on('boqs')->cascadeOnDelete();

            $table->foreign('previous_claim_id')->references('id')->on('boq_progress_claims')->nullOnDelete();

            // These tables should exist in your system
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_progress_claims');
    }
};
