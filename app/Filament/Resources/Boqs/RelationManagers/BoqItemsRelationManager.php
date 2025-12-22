<?php

namespace App\Filament\Resources\Boqs\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BoqItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Table $table): Table
    {
        return $table
            // ✅ ترتيب العرض حسب sort_order ثم id
            ->defaultSort('sort_order', 'asc')
            ->defaultSort('id', 'asc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتيب')
                    ->sortable(),

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
                    ->form([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\Select::make('work_item_id')
                            ->label('بند العمل')
                            ->relationship('workItem', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

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
                            ->required(),

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

                DeleteAction::make(),
            ]);
    }
}
