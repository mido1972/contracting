<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class ExportBoqPdf
{
    public function __construct(
        private readonly BoqReport $report
    ) {}

    public function generateToCache(int $boqId): string
    {
        @set_time_limit(300);

        $data = $this->report->build($boqId);

        $dir = storage_path('app/pdf-cache/boqs');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $pdfPath = $dir . DIRECTORY_SEPARATOR . "boq_{$boqId}.pdf";

        // ✅ خطوط Cairo (مرة واحدة فقط)
        $fontCss = Cache::rememberForever('cairo_font_css_v1', function () {
            $regularPath = public_path('fonts/cairo/Cairo-Regular.ttf');
            $boldPath    = public_path('fonts/cairo/Cairo-Bold.ttf');

            if (! File::exists($regularPath)) {
                return '/* Cairo Regular font missing */';
            }

            $regularB64 = base64_encode(File::get($regularPath));
            $boldB64    = File::exists($boldPath) ? base64_encode(File::get($boldPath)) : null;

            $css = "@font-face{font-family:\"Cairo\";src:url(\"data:font/ttf;base64,{$regularB64}\") format(\"truetype\");font-weight:400;font-style:normal;}";
            if ($boldB64) {
                $css .= "@font-face{font-family:\"Cairo\";src:url(\"data:font/ttf;base64,{$boldB64}\") format(\"truetype\");font-weight:700;font-style:normal;}";
            }
            $css .= "html,body{font-family:\"Cairo\", Arial, \"DejaVu Sans\", sans-serif !important;}";

            return $css;
        });

        $html = view('reports.boq.print', $data + ['isPdf' => 1])->render();
        $html = $this->injectCssIntoHead($html, $fontCss);

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

        return $pdfPath;
    }

    private function injectCssIntoHead(string $html, string $css): string
    {
        $styleTag = "<style>{$css}</style>";
        if (stripos($html, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $styleTag . '</head>', $html, 1);
        }
        return $styleTag . $html;
    }
}
