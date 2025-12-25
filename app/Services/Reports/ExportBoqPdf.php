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

        // Flag to let the Blade know this is PDF mode (hide buttons, tweak layout if needed)
        $data['isPdf'] = true;

        $pdf = Pdf::loadView('reports.boq.print', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                // Arabic-friendly font
                'defaultFont' => 'DejaVu Sans',

                // Better CSS/HTML parsing
                'isHtml5ParserEnabled' => true,

                // Allow remote assets if you use e.g. images/fonts via url()
                'isRemoteEnabled' => true,

                // Rendering quality
                'dpi' => 96,
            ]);

        $filename = 'BOQ-' . ($data['boq']->code ?? $data['boq']->id) . '.pdf';

        return $pdf->download($filename);
    }
}
