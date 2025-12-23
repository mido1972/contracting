<?php

namespace App\Filament\Resources\Boqs\Pages;

use App\Filament\Resources\Boqs\BoqResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBoq extends CreateRecord
{
    protected static string $resource = BoqResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user) {
            $data['company_id'] = (int) $user->current_company_id;
            $data['branch_id']  = (int) $user->current_branch_id;
        }

        return $data;
    }
}
