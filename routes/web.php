<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Filament\BranchSwitchController;
use App\Models\Boq;
use App\Exports\BoqItemsExport;
use App\Services\Reports\BoqReport;
use App\Services\Reports\ExportBoqPdf;

use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
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

        /**
         * Enforce tenant context on BOQ
         */
        $enforceBoqContext = function (Boq $boq): void {
            $user = auth()->user();

            if (! $user) {
                abort(403);
            }

            // Strongest: branch
            if (filled($user->current_branch_id)) {
                abort_unless(
                    (int) $boq->branch_id === (int) $user->current_branch_id,
                    404
                );
                return;
            }

            // Fallback: company
            if (filled($user->current_company_id)) {
                abort_unless(
                    (int) $boq->company_id === (int) $user->current_company_id,
                    404
                );
                return;
            }

            abort(403, 'No active context.');
        };

        /*
        |--------------------------------------------------------------------------
        | BOQ Reports
        |--------------------------------------------------------------------------
        */
        Route::prefix('boqs')->group(function () use ($enforceBoqContext) {

            Route::get('{boq}/print', function (Boq $boq) use ($enforceBoqContext) {
                $enforceBoqContext($boq);

                $data = app(BoqReport::class)->build($boq->id);

                return view('reports.boq.print', $data);
            })
                ->whereNumber('boq')
                ->name('reports.boq.print');

            Route::get('{boq}/pdf', function (Boq $boq) use ($enforceBoqContext) {
                $enforceBoqContext($boq);

                return app(ExportBoqPdf::class)->handle($boq->id);
            })
                ->whereNumber('boq')
                ->name('reports.boq.pdf');

            Route::get('{boq}/excel', function (Boq $boq) use ($enforceBoqContext) {
                $enforceBoqContext($boq);

                $filename = 'BOQ-Items-' . $boq->id . '.xlsx';

                return Excel::download(
                    new BoqItemsExport($boq->id),
                    $filename
                );
            })
                ->whereNumber('boq')
                ->name('reports.boq.excel');
        });
    });
