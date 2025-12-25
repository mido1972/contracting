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
        $enforceBoqContext = function ($boq): void {

            /** @var \App\Models\User|null $user */
            $user = request()->user();

            if (! $user) {
                abort(403);
            }

            // دعم حالتي: Route Model Binding أو ID
            if (! $boq instanceof Boq) {
                $boq = Boq::findOrFail((int) $boq);
            }

            // Strongest scope: branch
            if (filled($user->current_branch_id)) {
                abort_unless(
                    (int) $boq->branch_id === (int) $user->current_branch_id,
                    404
                );
                return;
            }

            // Fallback scope: company
            if (filled($user->current_company_id)) {
                abort_unless(
                    (int) $boq->company_id === (int) $user->current_company_id,
                    404
                );
                return;
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

                $enforceBoqContext($boq);

                // تأكيد إن اللي راجع Model
                $boq = $boq instanceof Boq ? $boq : Boq::findOrFail((int) $boq);

                return view(
                    'reports.boq.print',
                    app(BoqReport::class)->build($boq->id)
                );
            })
                ->whereNumber('boq')
                ->name('reports.boq.print');

            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/pdf', function ($boq) use ($enforceBoqContext) {

                $enforceBoqContext($boq);

                $boq = $boq instanceof Boq ? $boq : Boq::findOrFail((int) $boq);

                return app(ExportBoqPdf::class)->handle($boq->id);
            })
                ->whereNumber('boq')
                ->name('reports.boq.pdf');

            /*
            |--------------------------------------------------------------------------
            | Excel
            |--------------------------------------------------------------------------
            */
            Route::get('{boq}/excel', function ($boq) use ($enforceBoqContext) {

                $enforceBoqContext($boq);

                $boq = $boq instanceof Boq ? $boq : Boq::findOrFail((int) $boq);

                return Excel::download(
                    new BoqItemsExport($boq->id),
                    'BOQ-Items-' . $boq->id . '.xlsx'
                );
            })
                ->whereNumber('boq')
                ->name('reports.boq.excel');
        });
    });
