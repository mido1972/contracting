<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportBoqPdf
{
    public function __construct(private readonly BoqReport $report)
    {
    }

    public function handle(int $boqId): Response
    {
        $data = $this->report->build($boqId);

        $pdf = Pdf::loadView('reports.boq.print', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans', // good Arabic support
                'isRemoteEnabled' => true,       // allows loading remote assets (if any)
            ]);

        $filename = 'BOQ-' . ($data['boq']->code ?? $data['boq']->id) . '.pdf';

        return $pdf->download($filename);
    }
}
