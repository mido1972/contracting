<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_progress_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('claim_id');     // boq_progress_claims.id
            $table->unsignedBigInteger('boq_item_id');  // boq_items.id

            // Quantities
            $table->decimal('qty_previous', 14, 3)->default(0);
            $table->decimal('qty_current', 14, 3)->default(0);
            $table->decimal('qty_total', 14, 3)->default(0);

            // Snapshot pricing
            $table->decimal('unit_price', 14, 2)->default(0);

            // Amounts
            $table->decimal('amount_current', 14, 2)->default(0);
            $table->decimal('amount_total', 14, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            // One row per (claim, boq_item)
            $table->unique(['claim_id', 'boq_item_id'], 'bpi_unique_claim_item');

            $table->index(['claim_id'], 'bpi_claim_idx');
            $table->index(['boq_item_id'], 'bpi_boqitem_idx');

            // FKs
            $table->foreign('claim_id')->references('id')->on('boq_progress_claims')->cascadeOnDelete();
            $table->foreign('boq_item_id')->references('id')->on('boq_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_progress_items');
    }
};
