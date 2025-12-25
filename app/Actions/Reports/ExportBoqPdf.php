<?php

namespace App\Actions\Reports;

use App\Services\Reports\BoqReport;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportBoqPdf
{
    public function handle(int $boqId)
    {
        $data = app(BoqReport::class)->build($boqId);

        $pdf = Pdf::loadView('reports.boq.print', $data)
            ->setPaper('a4');

        $name = 'BOQ-' . ($data['boq']->code ?? $data['boq']->id) . '.pdf';

        return $pdf->download($name);
    }
}
