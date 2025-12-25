<?php

namespace App\Filament\Resources\Boqs\Pages;

use App\Filament\Resources\Boqs\BoqResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Builder;

class EditBoq extends EditRecord
{
    protected static string $resource = BoqResource::class;

    /**
     * ✅ Security:
     * منع تعديل مقايسة خارج الفرع / الشركة الحالية
     * أي محاولة وصول مباشر → 404
     */
    protected function getRecordQuery(): Builder
    {
        return parent::getRecordQuery()
            ->forCurrentContext();
    }

    /**
     * ✅ Safety (Server-side):
     * منع حفظ أي تعديل على المقايسة لو ليست DRAFT
     * (حتى لو زرار Save ظاهر لأي سبب أو محاولة POST مباشرة)
     */
    protected function beforeSave(): void
    {
        if ((string) $this->record->status !== 'DRAFT') {
            Notification::make()
                ->title('غير مسموح')
                ->body('لا يمكن تعديل المقايسة إلا وهي في حالة مسودة (DRAFT).')
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    /**
     * ✅ تحسين UX:
     * إظهار المشروع + الفرع أعلى الصفحة
     */
    protected function getHeaderSubheading(): ?string
    {
        $boq = $this->record;

        return sprintf(
            'المشروع: %s | الفرع: %s',
            $boq->project?->name ?? '-',
            $boq->branch?->name_ar
                ?? $boq->branch?->name_en
                ?? '-'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('إرسال')
                ->visible(fn () => (string) $this->record->status === 'DRAFT')
                ->requiresConfirmation()
                ->action(function () {
                    // Safety
                    if ((string) $this->record->status !== 'DRAFT') {
                        Notification::make()
                            ->title('غير مسموح')
                            ->body('لا يمكن إرسال المقايسة إلا وهي مسودة (DRAFT).')
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'status' => 'SUBMITTED',
                    ]);

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
                    // Safety
                    if ((string) $this->record->status !== 'SUBMITTED') {
                        Notification::make()
                            ->title('غير مسموح')
                            ->body('لا يمكن إرجاع المقايسة لمسودة إلا وهي مُرسلة (SUBMITTED).')
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'status' => 'DRAFT',
                    ]);

                    Notification::make()
                        ->title('تم إرجاع المقايسة لمسودة')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            DeleteAction::make()
                ->visible(fn () => (string) $this->record->status === 'DRAFT'),
        ];
    }
}
