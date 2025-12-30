<?php

namespace App\Filament\Resources\BoqProgressClaims\Pages;

use App\Filament\Resources\BoqProgressClaims\BoqProgressClaimResource;
use App\Services\Boq\ProgressClaimBuilder;
use App\Services\Boq\ProgressClaimCalculator;
use Filament\Resources\Pages\CreateRecord;

class CreateBoqProgressClaim extends CreateRecord
{
    protected static string $resource = BoqProgressClaimResource::class;

    protected function afterCreate(): void
    {
        // ✅ توليد البنود من BOQ
        app(ProgressClaimBuilder::class)->initializeItems($this->record);

        // ✅ حساب الإجماليات بعد توليد البنود
        app(ProgressClaimCalculator::class)->recalculate($this->record);
    }
}
