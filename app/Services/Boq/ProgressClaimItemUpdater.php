<?php

namespace App\Services\Boq;

use App\Models\BoqProgressClaim;
use App\Models\BoqProgressItem;
use Illuminate\Support\Facades\DB;

class ProgressClaimItemUpdater
{
    /**
     * Update qty_current for a claim item.
     * - clamps negative to 0
     * - clamps to remaining BOQ contract quantity (if available)
     * - updates qty_total + amounts
     * - recalculates claim header totals
     */
    public function updateCurrentQty(int $claimItemId, float $qtyCurrent, ?string $notes = null): BoqProgressItem
    {
        return DB::transaction(function () use ($claimItemId, $qtyCurrent, $notes) {

            /** @var BoqProgressItem $item */
            $item = BoqProgressItem::query()
                ->lockForUpdate()
                ->with('boqItem') // ✅ علشان نعرف كمية المقايسة
                ->findOrFail($claimItemId);

            // ✅ Lock claim too (avoid concurrent totals issues)
            /** @var BoqProgressClaim $claim */
            $claim = BoqProgressClaim::query()
                ->lockForUpdate()
                ->findOrFail($item->claim_id);

            $qtyPrev   = (float) $item->qty_previous;
            $unitPrice = (float) $item->unit_price;

            // ✅ منع قيم سالبة
            $qtyCurrent = max(0.0, (float) $qtyCurrent);

            // ✅ Clamp to BOQ contract quantity if present
            $boqItem = $item->boqItem;
            if ($boqItem) {
                // حاول نقرأ الكمية المتعاقد عليها من أي اسم شائع بدون ما نكسر
                $contractQty = null;

                foreach (['qty_contract', 'contract_qty', 'quantity', 'qty'] as $field) {
                    if (isset($boqItem->{$field}) && $boqItem->{$field} !== null) {
                        $contractQty = (float) $boqItem->{$field};
                        break;
                    }
                }

                if ($contractQty !== null && $contractQty > 0) {
                    // لا نسمح أن إجمالي (سابق + حالي) يتجاوز كمية العقد
                    $maxCurrent = max(0.0, $contractQty - $qtyPrev);
                    if ($qtyCurrent > $maxCurrent) {
                        $qtyCurrent = $maxCurrent;
                    }
                }
            }

            $qtyTotal = $qtyPrev + $qtyCurrent;

            $amountCurrent = round($qtyCurrent * $unitPrice, 2);
            $amountTotal   = round($qtyTotal * $unitPrice, 2);

            $item->forceFill([
                'qty_current'     => $qtyCurrent,
                'qty_total'       => $qtyTotal,
                'amount_current'  => $amountCurrent,
                'amount_total'    => $amountTotal,
                'notes'           => $notes ?? $item->notes,
            ])->save();

            // ✅ بعد تحديث أي بند: أعد حساب الهيدر (A/B/C/D/VAT/Net)
            app(ProgressClaimCalculator::class)->recalculate($claim->fresh());

            return $item->fresh();
        });
    }
}
