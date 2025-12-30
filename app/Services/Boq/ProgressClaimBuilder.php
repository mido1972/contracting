<?php

namespace App\Services\Boq;

use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\BoqProgressClaim;
use App\Models\BoqProgressItem;
use Illuminate\Support\Facades\DB;

class ProgressClaimBuilder
{
    /**
     * Initialize items for a new progress claim from BOQ.
     * - Snapshot unit_price
     * - qty_previous from last claim (if any)
     * - qty_current = 0
     */
    public function initializeItems(BoqProgressClaim $claim): void
    {
        DB::transaction(function () use ($claim) {

            // Lock items table for safe bulk insert
            DB::statement('LOCK TABLE boq_progress_items IN SHARE ROW EXCLUSIVE MODE');

            $boq = Boq::with('items')->findOrFail($claim->boq_id);

            // Last claim (previous)
            $prevClaim = BoqProgressClaim::query()
                ->where('boq_id', $claim->boq_id)
                ->where('company_id', $claim->company_id)
                ->where('branch_id', $claim->branch_id)
                ->where('claim_no', '<', $claim->claim_no)
                ->orderByDesc('claim_no')
                ->first();

            // Map previous quantities by boq_item_id
            $prevQtyMap = [];
            if ($prevClaim) {
                $prevItems = BoqProgressItem::query()
                    ->where('claim_id', $prevClaim->id)
                    ->get(['boq_item_id', 'qty_total']);

                foreach ($prevItems as $pi) {
                    $prevQtyMap[$pi->boq_item_id] = (float) $pi->qty_total;
                }
            }

            foreach ($boq->items as $boqItem) {
                $qtyPrev = $prevQtyMap[$boqItem->id] ?? 0.0;

                BoqProgressItem::create([
                    'claim_id'     => $claim->id,
                    'boq_item_id'  => $boqItem->id,
                    'qty_previous' => $qtyPrev,
                    'qty_current'  => 0,
                    'qty_total'    => $qtyPrev,
                    'unit_price'   => (float) $boqItem->unit_price,
                    'amount_current' => 0,
                    'amount_total'   => round($qtyPrev * (float) $boqItem->unit_price, 2),
                ]);
            }
        });
    }
}
