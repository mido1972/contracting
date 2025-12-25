<?php

namespace App\Services\Reports;

use App\Models\Boq;
use Illuminate\Support\Facades\Schema;

class BoqReport
{
    /**
     * Cache column existence checks.
     */
    private static ?bool $hasCurrencyCodeColumn = null;

    private function hasCurrencyCodeColumn(): bool
    {
        if (self::$hasCurrencyCodeColumn === null) {
            self::$hasCurrencyCodeColumn = Schema::hasColumn('boqs', 'currency_code');
        }

        return self::$hasCurrencyCodeColumn;
    }

    /**
     * Build BOQ report data for print / pdf.
     */
    public function build(int $boqId): array
    {
        // Select only guaranteed columns
        $columns = [
            'id',
            'code',
            'name',
            'status',
            'company_id',
            'branch_id',
            'project_id',
            'total_amount',
            'notes',
            'created_at',
            'updated_at',
        ];

        if ($this->hasCurrencyCodeColumn()) {
            $columns[] = 'currency_code';
        }

        $boq = Boq::query()
            ->select($columns)
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

        $items = $boq->items;

        $subtotal = (float) $items->sum('total_price');

        $totalAmount = (float) (
            filled($boq->total_amount)
                ? $boq->total_amount
                : $subtotal
        );

        $currency = 'SAR';
        if ($this->hasCurrencyCodeColumn() && filled($boq->currency_code)) {
            $currency = (string) $boq->currency_code;
        }

        return [
            'boq'          => $boq,
            'items'        => $items,
            'subtotal'     => $subtotal,
            'total_amount' => $totalAmount,
            'currency'     => $currency,
            'printed_at'   => now(),
        ];
    }
}
