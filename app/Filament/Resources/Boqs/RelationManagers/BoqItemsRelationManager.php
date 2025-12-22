<?php

namespace App\Filament\Resources\Boqs\RelationManagers;

use App\Models\WorkItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BoqItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected function isDraft(): bool
    {
        return (string) ($this->getOwnerRecord()?->status ?? '') === 'DRAFT';
    }

    public function table(Table $table): Table
    {
        return $table
            // ترتيب البنود
            ->modifyQueryUsing(fn ($query) => $query->orderBy('sort_order')->orderBy('id'))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتيب'),

                \Filament\Tables\Columns\TextColumn::make('workItem.name')
                    ->label('بند العمل')
                    ->wrap(),

                \Filament\Tables\Columns\TextColumn::make('unit.name')
                    ->label('الوحدة'),

                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3)),

                \Filament\Tables\Columns\TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                \Filament\Tables\Columns\TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->form([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب')
                            ->numeric()
                            ->required()
                            ->default(function (RelationManager $livewire): int {
                                $max = (int) $livewire->getOwnerRecord()
                                    ->items()
                                    ->max('sort_order');

                                return $max + 1;
                            }),

                        Forms\Components\Select::make('work_item_id')
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

                        Forms\Components\Select::make('unit_id')
                            ->label('الوحدة')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required()
                            ->default(1),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('سعر الوحدة')
                            ->numeric()
                            ->required()
                            ->default(0),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->form([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب')
                            ->numeric()
                            ->required(),

                        Forms\Components\Select::make('work_item_id')
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

                        Forms\Components\Select::make('unit_id')
                            ->label('الوحدة')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('سعر الوحدة')
                            ->numeric()
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                DeleteAction::make()
                    ->visible(fn () => $this->isDraft()),
            ]);
    }
}
