<?php

namespace App\Filament\Resources\BoqProgressClaims\Pages;

use App\Filament\Resources\BoqProgressClaims\BoqProgressClaimResource;
use App\Services\Boq\ProgressClaimCalculator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBoqProgressClaim extends EditRecord
{
    protected static string $resource = BoqProgressClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_draft')
                ->label('حفظ')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action(function () {
                    $this->save();

                    $this->record->status = 'DRAFT';
                    $this->record->save();

                    $claim = $this->record->fresh();
                    app(ProgressClaimCalculator::class)->recalculate($claim);

                    $this->redirect(BoqProgressClaimResource::getUrl('edit', ['record' => $claim->getKey()]));
                }),

            Action::make('submit')
                ->label('إرسال')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'DRAFT')
                ->action(function () {
                    $this->save();

                    $this->record->status = 'SUBMITTED';
                    $this->record->save();

                    $claim = $this->record->fresh();
                    app(ProgressClaimCalculator::class)->recalculate($claim);

                    $this->redirect(BoqProgressClaimResource::getUrl('edit', ['record' => $claim->getKey()]));
                }),

            Action::make('approve')
                ->label('اعتماد')
                ->icon('heroicon-o-shield-check')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['DRAFT', 'SUBMITTED'], true))
                ->requiresConfirmation()
                ->modalHeading('اعتماد المستخلص')
                ->modalDescription('بعد الاعتماد سيتم اعتبار هذا المستخلص معتمدًا ولن يتم تعديل الحالة إلا بإجراءات لاحقة.')
                ->action(function () {
                    $this->save();

                    $this->record->status = 'APPROVED';
                    $this->record->save();

                    $claim = $this->record->fresh();
                    app(ProgressClaimCalculator::class)->recalculate($claim);

                    $this->redirect(BoqProgressClaimResource::getUrl('edit', ['record' => $claim->getKey()]));
                }),

            Action::make('reject')
                ->label('رفض')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['DRAFT', 'SUBMITTED'], true))
                ->requiresConfirmation()
                ->modalHeading('رفض المستخلص')
                ->modalDescription('سيتم وضع المستخلص في حالة مرفوض.')
                ->action(function () {
                    $this->save();

                    $this->record->status = 'REJECTED';
                    $this->record->save();

                    $claim = $this->record->fresh();
                    app(ProgressClaimCalculator::class)->recalculate($claim);

                    $this->redirect(BoqProgressClaimResource::getUrl('edit', ['record' => $claim->getKey()]));
                }),

            Action::make('recalculate')
                ->label('إعادة حساب')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $claim = $this->record->fresh();
                    app(ProgressClaimCalculator::class)->recalculate($claim);

                    $this->redirect(BoqProgressClaimResource::getUrl('edit', ['record' => $claim->getKey()]));
                }),

            ViewAction::make()
                ->label('عرض')
                ->visible(false),

            DeleteAction::make()
                ->label('حذف')
                ->visible(fn () => ! in_array($this->record->status, ['APPROVED'], true)),
        ];
    }
}
