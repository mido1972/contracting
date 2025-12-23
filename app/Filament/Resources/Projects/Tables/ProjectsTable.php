<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('المشروع')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('geha.Geha_Name')
                    ->label('الجهة (ERP)')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('boq.name')
                    ->label('المقايسة')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc');
    }
}
