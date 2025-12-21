<?php

namespace App\Filament\Resources\Boqs\Pages;

use App\Filament\Resources\Boqs\BoqResource;
use App\Models\Boq;
use Filament\Resources\Pages\CreateRecord;

class CreateBoq extends CreateRecord
{
    protected static string $resource = BoqResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['code'])) {
            $nextId = Boq::query()->max('id');
            $nextId = ($nextId ?? 0) + 1;

            $data['code'] = 'BOQ-' . str_pad(
                (string) $nextId,
                6,
                '0',
                STR_PAD_LEFT
            );
        }

        return $data;
    }
}
