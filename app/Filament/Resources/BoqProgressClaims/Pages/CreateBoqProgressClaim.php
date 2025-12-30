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
        // ✅ 1) توليد بنود المستخلص من BOQ
        app(ProgressClaimBuilder::class)->initializeItems($this->record);

        // ✅ 2) حساب الإجماليات بعد توليد البنود
        $claim = $this->record->fresh();
        app(ProgressClaimCalculator::class)->recalculate($claim);

        // ✅ 3) تحديث السجل المحلي
        $this->record = $claim->fresh();
    }

    protected function getRedirectUrl(): string
    {
        // ✅ بعد الإنشاء افتح صفحة Edit مباشرة (زي برنامج الـ ERP)
        return BoqProgressClaimResource::getUrl('edit', [
            'record' => $this->record->getKey(),
        ]);
    }
}
