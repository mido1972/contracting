<?php

namespace App\Filament\Resources\Boqs\RelationManagers;

use App\Models\WorkItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoqItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود المقايسة';

    /**
     * الحالة الحالية للمقايسة
     */
    protected function isDraft(): bool
    {
        return (string) ($this->getOwnerRecord()?->status ?? '') === 'DRAFT';
    }

    /**
     * 🔒 Server-side safety
     * يمنع أي تعديل لو المقايسة مش Draft
     */
    protected function guardDraftOnly(): void
    {
        if (! $this->isDraft()) {
            Notification::make()
                ->title('غير مسموح')
                ->body('لا يمكن تعديل بنود المقايسة إلا وهي في حالة مسودة (DRAFT).')
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('sort_order')->orderBy('id'))
            ->columns([
                TextColumn::make('sort_order')
                    ->label('ترتيب'),

                TextColumn::make('workItem.name')
                    ->label('بند العمل')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('unit.name')
                    ->label('الوحدة'),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3)),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'name'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->before(fn () => $this->guardDraftOnly())
                    ->form([
                        TextInput::make('sort_order')
                            ->label('ترتيب')
                            ->numeric()
                            ->required()
                            ->default(function (RelationManager $livewire): int {
                                $max = (int) $livewire->getOwnerRecord()
                                    ->items()
                                    ->max('sort_order');

                                return $max + 1;
                            }),

                        Select::make('work_item_id')
                            ->label('بند العمل')
                            ->relationship('workItem', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    return;
                                }

                                $unitId = WorkItem::where('id', $state)->value('unit_id');
                                if ($unitId) {
                                    $set('unit_id', $unitId);
                                }
                            }),

                        Select::make('unit_id')
                            ->label('الوحدة')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required()
                            ->default(1),

                        TextInput::make('unit_price')
                            ->label('سعر الوحدة')
                            ->numeric()
                            ->required()
                            ->default(0),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->before(fn () => $this->guardDraftOnly())
                    ->form([
                        TextInput::make('sort_order')
                            ->label('ترتيب')
                            ->numeric()
                            ->required(),

                        Select::make('work_item_id')
                            ->label('بند العمل')
                            ->relationship('workItem', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    return;
                                }

                                $unitId = WorkItem::where('id', $state)->value('unit_id');
                                if ($unitId) {
                                    $set('unit_id', $unitId);
                                }
                            }),

                        Select::make('unit_id')
                            ->label('الوحدة')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required(),

                        TextInput::make('unit_price')
                            ->label('سعر الوحدة')
                            ->numeric()
                            ->required(),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                DeleteAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->before(fn () => $this->guardDraftOnly()),
            ]);
    }
}
