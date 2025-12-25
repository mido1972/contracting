<?php

namespace App\Exports;

use App\Models\BoqItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BoqItemsExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly int $boqId)
    {
    }

    public function headings(): array
    {
        return [
            'ترتيب',
            'البند',
            'الوحدة',
            'الكمية',
            'سعر الوحدة',
            'الإجمالي',
            'ملاحظات',
        ];
    }

    public function collection(): Collection
    {
        return BoqItem::query()
            ->with(['workItem:id,name', 'unit:id,name'])
            ->where('boq_id', $this->boqId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (BoqItem $it) {
                return [
                    (int) ($it->sort_order ?? 0),
                    (string) ($it->workItem?->name ?? ''),
                    (string) ($it->unit?->name ?? ''),
                    (float) ($it->quantity ?? 0),
                    (float) ($it->unit_price ?? 0),
                    (float) ($it->total_price ?? 0),
                    (string) ($it->notes ?? ''),
                ];
            });
    }
}
