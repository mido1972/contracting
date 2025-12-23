<?php

namespace App\Http\Controllers\Filament;

use App\Actions\SetCurrentBranch;
use Illuminate\Http\Request;

class BranchSwitchController
{
    public function __invoke(Request $request, SetCurrentBranch $action)
    {
        $request->validate([
            'branch_id' => ['required', 'integer'],
        ]);

        $action->handle((int) $request->branch_id);

        // رجّع لنفس الصفحة (refresh)
        return back();
    }
}
