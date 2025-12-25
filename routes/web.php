<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Filament\BranchSwitchController;
use App\Models\Boq;
use App\Exports\BoqItemsExport;
use App\Services\Reports\BoqReport;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Root Route (مهم جدًا لتفادي 404 والبطء العام)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/admin');
});

/*
|--------------------------------------------------------------------------
| Branch Switch (Filament)
|--------------------------------------------------------------------------
*/
Route::post('/filament/branch/switch', BranchSwitchController::class)
    ->middleware(['web', 'auth'])
    ->name('filament.branch.switch');

/*
|--------------------------------------------------------------------------
| Reports (BOQ): Print / PDF / Excel
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])
    ->prefix('reports')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Tenant Context Guard (Reusable)
        |--------------------------------------------------------------------------
        */
        $enforceBoqContext = function ($boq): Boq {

            /** @var \App\Models\User|null $user */
            $user = request()->user();
            abort_if(! $user, 403);

            if (! $boq instanceof Boq) {
                $boq = Boq::findOrFail((int) $boq);
            }

            if (filled($user->current_branch_id)) {
                abort_unless((int) $boq->branch_id === (int) $user->current_branch_id, 404);
                return $boq;
            }

            if (filled($user->current_company_id)) {
                abort_unless((int) $boq->company_id === (int) $user->current_company_id, 404);
                return $boq;
            }

            abort(403, 'No active tenant context.');
        };

        Route::prefix('boqs')->group(function () use ($enforceBoqContext) {

            /*
            |--------------------------------------------------------------------------
            | Print (HTML)
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/print', function ($boq) use ($enforceBoqContext) {

                $boq = $enforceBoqContext($boq);

                return view(
                    'reports.boq.print',
                    app(BoqReport::class)->build($boq->id)
                );
            })
                ->whereNumber('boq')
                ->name('reports.boq.print');

            /*
            |--------------------------------------------------------------------------
            | PDF (ASYNC) - Start Generation
            |--------------------------------------------------------------------------
            */
            Route::post('{boq}/pdf', function ($boq) use ($enforceBoqContext) {

                $boq = $enforceBoqContext($boq);

                dispatch(new \App\Jobs\GenerateBoqPdfJob($boq->id));

                return response()->json([
                    'status'       => 'queued',
                    'message'      => 'PDF generation started',
                    'status_url'   => url("/reports/boqs/{$boq->id}/pdf/status"),
                    'download_url' => url("/reports/boqs/{$boq->id}/pdf/download"),
                ]);
            })
                ->whereNumber('boq')
                ->name('reports.boq.pdf.start');

            /*
            |--------------------------------------------------------------------------
            | PDF - Status
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/pdf/status', function ($boq) use ($enforceBoqContext) {

                $boq = $enforceBoqContext($boq);

                $path = storage_path("app/pdf-cache/boqs/boq_{$boq->id}.pdf");

                return response()->json([
                    'ready' => file_exists($path),
                ]);
            })
                ->whereNumber('boq')
                ->name('reports.boq.pdf.status');

            /*
            |--------------------------------------------------------------------------
            | PDF - Download
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/pdf/download', function ($boq) use ($enforceBoqContext) {

                $boq = $enforceBoqContext($boq);

                $path = storage_path("app/pdf-cache/boqs/boq_{$boq->id}.pdf");

                // ✅ بدل 404: نرجّع 409 "لسه بيتجهز"
                if (! file_exists($path)) {
                    return response(
                        "PDF is not ready yet. Start generation via POST /reports/boqs/{$boq->id}/pdf then retry.",
                        409
                    );
                }

                $filename = 'BOQ-' . ($boq->code ?? $boq->id) . '.pdf';

                return response()->download($path, $filename, [
                    'Content-Type' => 'application/pdf',
                ]);
            })
                ->whereNumber('boq')
                ->name('reports.boq.pdf.download');

            /*
            |--------------------------------------------------------------------------
            | Legacy GET /pdf => redirect to download
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/pdf', function ($boq) {
                return redirect()->to("/reports/boqs/{$boq}/pdf/download");
            })
                ->whereNumber('boq')
                ->name('reports.boq.pdf.legacy');

            /*
            |--------------------------------------------------------------------------
            | Excel
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/excel', function ($boq) use ($enforceBoqContext) {

                $boq = $enforceBoqContext($boq);

                return Excel::download(
                    new BoqItemsExport($boq->id),
                    'BOQ-Items-' . $boq->id . '.xlsx'
                );
            })
                ->whereNumber('boq')
                ->name('reports.boq.excel');
        });
    });
