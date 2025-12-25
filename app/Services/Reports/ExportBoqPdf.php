<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class ExportBoqPdf
{
    public function __construct(
        private readonly BoqReport $report
    ) {}

    public function handle(int $boqId)
    {
        @set_time_limit(180);

        $data = $this->report->build($boqId);
        $boq  = $data['boq'];

        $code = $boq->code ?? $boq->id;
        $filename = 'BOQ-' . preg_replace('/[^A-Za-z0-9_\-]/', '-', (string) $code) . '.pdf';

        // ✅ افتح print view كـ URL حقيقي (أفضل من html())
        $url = URL::to("/reports/boqs/{$boqId}/print") . '?pdf=1';

        $tmpDir = storage_path('app/tmp');
        if (! File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $pdfPath = $tmpDir . DIRECTORY_SEPARATOR . 'boq_' . $boqId . '_' . Str::random(8) . '.pdf';

        // ✅ بيانات جاهزة للهيدر (بدون JS)
        $printedAt = now()->format('Y-m-d H:i');
        $fontRegular = URL::to('/fonts/cairo/Cairo-Regular.ttf');
        $fontBold    = URL::to('/fonts/cairo/Cairo-Bold.ttf');

        // ✅ args لتقليل التعليق/البطء قدر الإمكان
        $chromiumArgs = [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-background-networking',
            '--disable-background-timer-throttling',
            '--disable-renderer-backgrounding',
            '--disable-default-apps',
            '--disable-extensions',
            '--disable-sync',
            '--disable-translate',
        ];

        Browsershot::url($url)
            ->format('A4')
            // margins بالـ mm: top, right, bottom, left
            ->margins(12, 10, 14, 10)
            ->emulateMedia('print')
            ->showBackground()

            /**
             * ✅ بدل setDelay الكبير:
             * نخلي Chromium يستنى لحد ما الشبكة تهدى (الموارد الأساسية تخلص)
             * ده غالبًا بيحل Page.navigate timed out في صفحات الطباعة
             */
            ->waitUntilNetworkIdle()

            /**
             * ✅ timeout هنا للـ Browsershot نفسه
             * ✅ protocolTimeout للـ puppeteer/chrome protocol (اللي بيطلع "Page.navigate timed out")
             */
            ->timeout(180)
            ->setOption('protocolTimeout', 180000)

            ->showBrowserHeaderAndFooter()
            ->headerHtml($this->headerTemplate($printedAt, $fontRegular, $fontBold))
            ->footerHtml($this->footerTemplate($fontRegular))
            ->setOption('args', $chromiumArgs)

            // خيارات طباعة مهمة
            ->setOption('printBackground', true)
            ->setOption('preferCSSPageSize', true)

            // ✅ لا نحتاج delay بعد NetworkIdle
            ->setDelay(0)

            ->savePdf($pdfPath);

        return response()
            ->download($pdfPath, $filename, ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend(true);
    }

    private function headerTemplate(string $printedAt, string $fontRegularUrl, string $fontBoldUrl): string
    {
        // ملاحظة: Header/Footer بيبقوا "وثائق مستقلة" داخل Chromium
        // لذلك لازم نعرّف الخط هنا أيضًا عبر URL (مش file://)
        return <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
@font-face{
  font-family:"Cairo";
  src:url("{$fontRegularUrl}") format("truetype");
  font-weight:400;
  font-style:normal;
}
@font-face{
  font-family:"Cairo";
  src:url("{$fontBoldUrl}") format("truetype");
  font-weight:700;
  font-style:normal;
}

body{
  margin:0;
  padding:0 10mm;
  font-size:10px;
  font-family:"Cairo", Arial, "DejaVu Sans", sans-serif;
  color:#111;
}

.row{
  display:flex;
  justify-content:space-between;
  align-items:flex-end;
  border-bottom:1px solid #ddd;
  padding:6px 0;
}

.title{ font-weight:700; }

.meta{
  direction:ltr;
  unicode-bidi:isolate;
  font-size:9px;
  color:#555;
  white-space:nowrap;
}
</style>
</head>
<body>
  <div class="row">
    <div class="title">مقايسة أعمال</div>
    <div class="meta">{$printedAt}</div>
  </div>
</body>
</html>
HTML;
    }

    private function footerTemplate(string $fontRegularUrl): string
    {
        return <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
@font-face{
  font-family:"Cairo";
  src:url("{$fontRegularUrl}") format("truetype");
  font-weight:400;
  font-style:normal;
}

body{
  margin:0;
  padding:0 10mm;
  font-size:9px;
  font-family:"Cairo", Arial, "DejaVu Sans", sans-serif;
  color:#666;
}

.row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-top:1px solid #ddd;
  padding:6px 0;
}

.pages{
  direction:ltr;
  unicode-bidi:isolate;
  white-space:nowrap;
}
</style>
</head>
<body>
  <div class="row">
    <div>BOQ Report</div>
    <div class="pages">
      Page <span class="pageNumber"></span> / <span class="totalPages"></span>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
