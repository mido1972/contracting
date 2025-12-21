<?php

namespace App\Filament\Resources\Boqs\RelationManagers;

use Filament\Actions\CreateAction;
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
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
