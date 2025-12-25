<?php

namespace App\Services\Reports;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Response;

class ExportBoqPdf
{
    public function __construct(private readonly BoqReport $report)
    {
    }

    public function handle(int $boqId): Response
    {
        $data = $this->report->build($boqId);
        $data['isPdf'] = true;

        $filename = 'BOQ-' . ($data['boq']->code ?? $data['boq']->id) . '.pdf';

        $pdf = SnappyPdf::loadView('reports.boq.print', $data)
            ->setOption('encoding', 'utf-8')
            ->setOption('page-size', 'A4')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-right', '10mm')
            ->setOption('margin-bottom', '15mm')
            ->setOption('margin-left', '10mm')

            // allow local fonts/images via file:///
            ->setOption('enable-local-file-access', true)

            // use print css
            ->setOption('print-media-type', true)

            // rendering stability
            ->setOption('disable-smart-shrinking', true)
            ->setOption('load-error-handling', 'ignore')
            ->setOption('load-media-error-handling', 'ignore');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
