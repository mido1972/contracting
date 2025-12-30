<?php

namespace App\Services\Boq;

use App\Models\BoqProgressClaim;
use App\Models\BoqProgressItem;
use App\Models\BoqProgressDeduction;
use App\Models\BoqProgressTax;
use Illuminate\Support\Facades\DB;

class ProgressClaimCalculator
{
    /**
     * Recalculate claim totals to match Excel logic.
     *
     * A = Sum of items amount_total (cumulative to date)
     * B = Previous claims cumulative (latest claim before current)
     * C = Retention/Guarantee (percent or fixed) from deductions code = 'RETENTION'
     * D = Other deductions/additions (sum of deductions amounts excluding RETENTION)
     * VAT = tax on (A - B - C - D) by default
     * NET = (A - B - C - D) + VAT
     */
    public function recalculate(BoqProgressClaim $claim): void
    {
        DB::transaction(function () use ($claim) {

            // Refresh from DB to be safe
            $claim->refresh();

            // --------
            // A: cumulative total value up to date (from claim items snapshot)
            // --------
            $A = (float) BoqProgressItem::query()
                ->where('claim_id', $claim->id)
                ->sum('amount_total');

            // --------
            // B: previous cumulative (latest claim < current claim_no)
            // --------
            $prev = BoqProgressClaim::query()
                ->where('boq_id', $claim->boq_id)
                ->where('company_id', $claim->company_id)
                ->where('branch_id', $claim->branch_id)
                ->where('claim_no', '<', $claim->claim_no)
                ->orderByDesc('claim_no')
                ->first();

            $B = $prev ? (float) $prev->total_a_cumulative : 0.0;

            // --------
            // Deductions: Retention (C) + Others (D)
            // convention:
            // - RETENTION row => code = 'RETENTION' (percent or fixed)
            // - other rows => amounts stored in 'amount'
            // --------
            $C = 0.0;
            $D = 0.0;

            $deductions = BoqProgressDeduction::query()
                ->where('claim_id', $claim->id)
                ->get();

            // Base amount for deductions is the "current payable before VAT"
            // = A - B
            $base = max(0.0, $A - $B);

            foreach ($deductions as $d) {
                $code = strtoupper(trim((string) $d->code));
                $method = strtolower(trim((string) $d->method));
                $value = (float) $d->value;

                // If amount is already stored, trust it unless it's retention percent
                $amount = (float) $d->amount;

                if ($code === 'RETENTION') {
                    if ($method === 'percent') {
                        $amount = round(($base * $value) / 100, 2);
                    } elseif ($method === 'fixed') {
                        $amount = round($value, 2);
                    }
                    // Save computed retention amount for transparency
                    $d->forceFill(['amount' => $amount])->saveQuietly();
                    $C += $amount;
                } else {
                    // For others: if method percent, compute on base too
                    if ($method === 'percent') {
                        $amount = round(($base * $value) / 100, 2);
                        $d->forceFill(['amount' => $amount])->saveQuietly();
                    } elseif ($method === 'fixed') {
                        $amount = round($value, 2);
                        $d->forceFill(['amount' => $amount])->saveQuietly();
                    }
                    $D += $amount;
                }
            }

            // --------
            // Taxable = A - B - C - D  (Excel: صافي قيمة الأعمال قبل الضريبة)
            // --------
            $taxable = round(max(0.0, $A - $B - $C - $D), 2);

            // VAT (default 15%) - store/update single VAT row
            $vatRate = 15.00;
            $vat = round(($taxable * $vatRate) / 100, 2);

            $tax = BoqProgressTax::query()
                ->firstOrNew([
                    'claim_id' => $claim->id,
                    'tax_code' => 'VAT',
                ]);

            $tax->fill([
                'rate' => $vatRate,
                'taxable_amount' => $taxable,
                'tax_amount' => $vat,
            ]);

            $tax->save();

            // NET = taxable + VAT
            $net = round($taxable + $vat, 2);

            // Save header snapshot totals
            $claim->forceFill([
                'total_a_cumulative' => round($A, 2),
                'total_b_previous'   => round($B, 2),
                'total_c_retention'  => round($C, 2),
                'total_d_deductions' => round($D, 2),
                'vat_amount'         => $vat,
                'net_payable'        => $net,
            ])->saveQuietly();
        });
    }
}
