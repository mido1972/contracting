<?php

namespace App\Jobs;

use App\Services\Reports\ExportBoqPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateBoqPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(public int $boqId) {}

    public function handle(ExportBoqPdf $exporter): void
    {
        $lock = Cache::lock("boq_pdf_generate_{$this->boqId}", 120);

        if (! $lock->get()) {
            return;
        }

        try {
            $exporter->generateToCache($this->boqId);
        } finally {
            optional($lock)->release();
        }
    }
}
