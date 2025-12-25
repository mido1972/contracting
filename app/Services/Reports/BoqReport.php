<?php

namespace App\Services\Reports;

use App\Models\Boq;

class BoqReport
{
    /**
     * Build BOQ report data for print/pdf.
     * Returns array to be passed directly to view(..., $data)
     */
    public function build(int $boqId): array
    {
        $boq = Boq::query()
            ->with([
                'company',
                'branch',
                'project',
                'items' => function ($q) {
                    $q->with(['workItem', 'unit'])
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->findOrFail($boqId);

        // Ensure totals are consistent (optional, remove if you don’t want auto-fix)
        // $boq->recalculateTotals();

        $items = $boq->items;

        $subtotal = (float) $items->sum('total_price');

        return [
            'boq' => $boq,
            'items' => $items,
            'subtotal' => $subtotal,
            'total_amount' => (float) ($boq->total_amount ?? $subtotal),
            'currency' => $boq->currencyCode(),
            'printed_at' => now(),
        ];
    }
}
