<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\Project;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoqsRelationManager extends RelationManager
{
    /**
     * Project::boqs()
     */
    protected static string $relationship = 'boqs';

    protected static ?string $title = 'المقايسات';

    /**
     * Filament v4 uses Schema (NOT Forms\Form)
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود المقايسة')
                    ->maxLength(50)
                    ->helperText('اختياري (يمكن توليده لاحقًا)'),

                TextInput::make('name')
                    ->label('اسم المقايسة')
                    ->required()
                    ->maxLength(255),

                // ✅ Safety/UX: نخلي تغيير الحالة من صفحة BOQ الأساسية (EditBoq Actions)
                // عشان مايحصلش تغيير حالة بالخطأ من داخل المشروع.
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'DRAFT'     => 'مسودة',
                        'SUBMITTED' => 'مُرسلة',
                        'AWARDED'   => 'تمت الترسية',
                        'CANCELLED' => 'ملغاة',
                    ])
                    ->default('DRAFT')
                    ->required()
                    ->disabled(),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        $currency = fn (): string =>
            $this->getOwnerRecord()?->currencyCode()
            ?? config('app.currency_default', 'SAR');

        return $table
            // ✅ Double-safety داخل RelationManager
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->forCurrentContext();
            })
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT'     => 'gray',
                        'SUBMITTED' => 'warning',
                        'AWARDED'   => 'success',
                        'CANCELLED' => 'danger',
                        default     => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->money($currency)
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                CreateAction::make()
                    // ✅ إنشاء BOQ من داخل المشروع: Draft فقط
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();

                        // تثبيت الـ context تلقائيًا
                        $data['company_id'] = $project->company_id;
                        $data['branch_id']  = $project->branch_id;
                        $data['project_id'] = $project->id;

                        // Safety: إنشاء من هنا يكون Draft دائمًا
                        $data['status'] = 'DRAFT';

                        return $data;
                    }),
            ])
            ->actions([
                ViewAction::make(),

                // ✅ تعديل من داخل المشروع: Draft فقط
                EditAction::make()
                    ->visible(fn ($record): bool => ($record->status ?? null) === 'DRAFT'),
            ])
            ->bulkActions([
                // ✅ حذف جماعي: Draft فقط + منع التنفيذ لو فيه Records غير Draft
                DeleteBulkAction::make()
                    ->before(function ($action, $records): void {
                        $hasNonDraft = $records->contains(fn ($r) => ($r->status ?? null) !== 'DRAFT');

                        if ($hasNonDraft) {
                            Notification::make()
                                ->title('غير مسموح')
                                ->body('الحذف الجماعي مسموح للمسودات فقط (Draft).')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
            ]);
    }
}
