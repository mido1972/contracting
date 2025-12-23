<?php

namespace App\Actions;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class SetCurrentBranch
{
    public function handle(int $branchId): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        // تأكد أن الفرع تابع لنفس الشركة الحالية (أمان)
        $branch = Branch::query()
            ->where('id', $branchId)
            ->where('company_id', $user->current_company_id)
            ->first();

        if (! $branch) {
            return;
        }

        $user->forceFill([
            'current_branch_id' => $branch->id,
        ])->save();
    }
}
