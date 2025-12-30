<?php

namespace App\Services\Boq;

use App\Models\BoqProgressClaim;
use App\Models\BoqProgressItem;
use Illuminate\Support\Facades\DB;

class ProgressClaimItemUpdater
{
    public function updateCurrentQty(int $claimItemId, float $qtyCurrent): BoqProgressItem
    {
        return DB::transaction(function () use ($claimItemId, $qtyCurrent) {

            /** @var BoqProgressItem $item */
            $item = BoqProgressItem::query()->lockForUpdate()->findOrFail($claimItemId);

            $qtyPrev = (float) $item->qty_previous;
            $unitPrice = (float) $item->unit_price;

            // ✅ منع قيم سالبة
            $qtyCurrent = max(0.0, $qtyCurrent);

            $qtyTotal = $qtyPrev + $qtyCurrent;

            $amountCurrent = round($qtyCurrent * $unitPrice, 2);
            $amountTotal   = round($qtyTotal * $unitPrice, 2);

            $item->forceFill([
                'qty_current'     => $qtyCurrent,
                'qty_total'       => $qtyTotal,
                'amount_current'  => $amountCurrent,
                'amount_total'    => $amountTotal,
            ])->save();

            // ✅ بعد تحديث أي بند: أعد حساب الهيدر (A/B/C/D/VAT/Net)
            $claim = BoqProgressClaim::query()->findOrFail($item->claim_id);
            app(ProgressClaimCalculator::class)->recalculate($claim);

            return $item->fresh();
        });
    }
}
