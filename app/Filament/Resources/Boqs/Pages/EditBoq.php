<?php

namespace App\Filament\Resources\Boqs\Pages;

use App\Filament\Resources\Boqs\BoqResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBoq extends EditRecord
{
    protected static string $resource = BoqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('إرسال')
                ->visible(fn () => (string) $this->record->status === 'DRAFT')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'SUBMITTED']);

                    Notification::make()
                        ->title('تم إرسال المقايسة')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Action::make('back_to_draft')
                ->label('إرجاع لمسودة')
                ->visible(fn () => (string) $this->record->status === 'SUBMITTED')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'DRAFT']);

                    Notification::make()
                        ->title('تم إرجاع المقايسة لمسودة')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            DeleteAction::make(),
        ];
    }
}
