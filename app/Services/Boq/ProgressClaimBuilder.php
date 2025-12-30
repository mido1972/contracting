<?php

namespace App\Services\Boq;

use App\Models\Boq;
use App\Models\BoqProgressClaim;
use App\Models\BoqProgressItem;
use Illuminate\Support\Facades\DB;

class ProgressClaimBuilder
{
    /**
     * Initialize items for a new progress claim from BOQ.
     * - Snapshot unit_price
     * - qty_previous from previous_claim_id (last APPROVED)
     * - qty_current = 0
     *
     * Safety:
     * - If called twice, it will rebuild items (delete + insert) inside one transaction.
     */
    public function initializeItems(BoqProgressClaim $claim): void
    {
        DB::transaction(function () use ($claim) {
            // ✅ Lock table for safe rebuild (PostgreSQL)
            DB::statement('LOCK TABLE boq_progress_items IN SHARE ROW EXCLUSIVE MODE');

            // ✅ Always rebuild to avoid duplicates if initialize called twice
            BoqProgressItem::query()
                ->where('claim_id', $claim->id)
                ->delete();

            $boq = Boq::query()
                ->with(['items' => function ($q) {
                    $q->orderBy('id');
                }])
                ->findOrFail($claim->boq_id);

            // ✅ Use previous_claim_id ONLY (should point to last APPROVED)
            $prevQtyMap = [];

            if (! empty($claim->previous_claim_id)) {
                $prevItems = BoqProgressItem::query()
                    ->where('claim_id', $claim->previous_claim_id)
                    ->get(['boq_item_id', 'qty_total']);

                foreach ($prevItems as $pi) {
                    $prevQtyMap[(int) $pi->boq_item_id] = (float) $pi->qty_total;
                }
            }

            $now = now();
            $rows = [];

            foreach ($boq->items as $boqItem) {
                $unitPrice = (float) $boqItem->unit_price;
                $qtyPrev   = (float) ($prevQtyMap[(int) $boqItem->id] ?? 0.0);

                $amountTotal = round($qtyPrev * $unitPrice, 2);

                $rows[] = [
                    'claim_id'        => (int) $claim->id,
                    'boq_item_id'     => (int) $boqItem->id,

                    'qty_previous'    => $qtyPrev,
                    'qty_current'     => 0,
                    'qty_total'       => $qtyPrev,

                    'unit_price'      => $unitPrice,
                    'amount_current'  => 0,
                    'amount_total'    => $amountTotal,

                    'notes'           => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (! empty($rows)) {
                DB::table('boq_progress_items')->insert($rows);
            }
        });
    }
}
