<?php

namespace App\Filament\Resources\BoqProgressClaims\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class BoqProgressClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('boq.code')
                    ->label('رقم المقايسة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('claim_no')
                    ->label('رقم المستخلص')
                    ->sortable(),

                TextColumn::make('claim_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('net_payable')
                    ->label('الصافي')
                    ->money('SAR')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')

            // ✅ Filament v4 actions
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
