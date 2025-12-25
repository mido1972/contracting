<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Filament\BranchSwitchController;
use App\Models\Boq;
use App\Exports\BoqItemsExport;
use App\Services\Reports\BoqReport;
use App\Jobs\GenerateBoqPdfJob;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Root Route
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
| Reports (BOQ): Print / PDF / Excel — SYNC PDF
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])
    ->prefix('reports')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Tenant Context Guard
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
            | PDF (SYNC) — Generate (if missing) then Download
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/pdf/download', function ($boq) use ($enforceBoqContext) {

                $boq = $enforceBoqContext($boq);

                $path = storage_path("app/pdf-cache/boqs/boq_{$boq->id}.pdf");

                if (! file_exists($path)) {
                    dispatch_sync(new GenerateBoqPdfJob($boq->id));
                }

                if (! file_exists($path)) {
                    abort(500, 'PDF generation failed.');
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
                    'BOQ-Items-' . ($boq->code ?? $boq->id) . '.xlsx'
                );
            })
                ->whereNumber('boq')
                ->name('reports.boq.excel');
        });
    });
