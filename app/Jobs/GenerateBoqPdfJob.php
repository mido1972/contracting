<?php

namespace App\Jobs;

use App\Services\Reports\ExportBoqPdf;

/**
 * ⚠️ This job is currently DISABLED.
 *
 * PDF generation is handled synchronously via routes.
 * This class is kept فقط للتوافق المستقبلي لو احتجنا Async مرة تانية.
 */
class GenerateBoqPdfJob
{
    public function __construct(public int $boqId) {}

    /**
     * Intentionally left blank.
     * Do NOT generate PDF asynchronously.
     */
    public function handle(ExportBoqPdf $exporter): void
    {
        // no-op
    }
}
