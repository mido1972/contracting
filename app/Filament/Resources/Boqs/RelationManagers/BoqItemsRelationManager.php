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
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')->label('#'),

                \Filament\Tables\Columns\TextColumn::make('workItem.name')
                    ->label('بند العمل')
                    ->wrap(),

                \Filament\Tables\Columns\TextColumn::make('unit.name')
                    ->label('الوحدة'),

                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية'),

                \Filament\Tables\Columns\TextColumn::make('unit_price')
                    ->label('سعر الوحدة'),

                \Filament\Tables\Columns\TextColumn::make('total_price')
                    ->label('الإجمالي'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
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
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->form([
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
                    ]),

                DeleteAction::make(),
            ]);
    }
}
