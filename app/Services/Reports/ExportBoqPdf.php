<?php

namespace App\Services\Reports;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class ExportBoqPdf
{
    public function __construct(
        private readonly BoqReport $report
    ) {}

    /**
     * Generate PDF to cache and return full path.
     */
    public function generateToCache(int $boqId): string
    {
        @set_time_limit(300);

        $data = $this->report->build($boqId);

        $dir = storage_path('app/pdf-cache/boqs');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $pdfPath = $dir . DIRECTORY_SEPARATOR . "boq_{$boqId}.pdf";

        // Render HTML once (print view already optimized)
        $html = view('reports.boq.print', $data + ['isPdf' => 1])->render();

        // 1) Try Browsershot if available (optional)
        if ($this->canUseBrowsershot()) {
            try {
                $args = [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-gpu',
                    '--no-first-run',
                    '--no-default-browser-check',
                    '--disable-extensions',
                    '--disable-sync',
                    '--disable-translate',
                    '--disable-background-networking',
                    '--disable-background-timer-throttling',
                    '--disable-renderer-backgrounding',
                ];

                Browsershot::html($html)
                    ->format('A4')
                    ->margins(0, 0, 0, 0)
                    ->emulateMedia('print')
                    ->showBackground()
                    ->setOption('waitUntil', 'load')
                    ->timeout(300)
                    ->setOption('protocolTimeout', 300000)
                    ->setOption('args', $args)
                    ->setOption('printBackground', true)
                    ->setOption('preferCSSPageSize', true)
                    ->savePdf($pdfPath);

                if (File::exists($pdfPath) && File::size($pdfPath) > 1024) {
                    return $pdfPath;
                }
            } catch (\Throwable $e) {
                // fallback to Dompdf below
            }
        }

        // 2) Fallback: Dompdf (stable)
        $this->renderWithDompdf($html, $pdfPath);

        return $pdfPath;
    }

    /**
     * Generate if missing then download response.
     */
    public function download(int $boqId)
    {
        $path = storage_path("app/pdf-cache/boqs/boq_{$boqId}.pdf");

        if (! File::exists($path) || File::size($path) < 1024) {
            $path = $this->generateToCache($boqId);
        }

        if (! File::exists($path)) {
            abort(500, 'PDF generation failed.');
        }

        $data = $this->report->build($boqId);
        $boq = $data['boq'];

        $filename = 'BOQ-' . ($boq->code ?? $boq->id) . '.pdf';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function renderWithDompdf(string $html, string $pdfPath): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        File::put($pdfPath, $dompdf->output());
    }

    /**
     * Browsershot needs Node + Chromium/Puppeteer.
     * If not available, we skip it entirely.
     */
    private function canUseBrowsershot(): bool
    {
        // simple environment guards (don’t crash)
        // If node is not installed OR puppeteer/chrome missing, Browsershot will throw.
        // We just allow try/catch but this reduces noisy failures.
        $node = trim((string) @shell_exec('node -v 2>NUL'));
        if ($node === '') {
            $node = trim((string) @shell_exec('node -v 2>/dev/null'));
        }
        return Str::startsWith($node, 'v');
    }
}
