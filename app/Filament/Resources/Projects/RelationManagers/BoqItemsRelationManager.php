<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;

class BoqItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'boqItems';

    public function table(Table $table): Table
    {
        $currency = fn (): string => $this->getOwnerRecord()?->currencyCode() ?? 'SAR';

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['workItem', 'unit']))
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('workItem.name')
                    ->label('البند')
                    ->wrap()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('unit.name')
                    ->label('الوحدة')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money($currency)
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->money($currency)
                    ->summarize([
                        Sum::make()
                            ->label('الإجمالي')
                            ->money($currency),
                    ])
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
