<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Filament\BranchSwitchController;

Route::get('/', function () {
    return view('welcome');
});

// Branch Switch (Filament)
Route::post('/filament/branch/switch', BranchSwitchController::class)
    ->middleware(['web', 'auth'])
    ->name('filament.branch.switch');
