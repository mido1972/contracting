<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;

class BoqItemsRelationManager extends RelationManager
{
    /**
     * ✅ بدل boqItems (القديم المبني على project.boq_id)
     * هنستخدم علاقة صحيحة: Project -> boqs -> boq_items
     */
    protected static string $relationship = 'boqItemsViaBoqs';

    protected static ?string $title = 'بنود المقايسات';

    public function table(Table $table): Table
    {
        $currency = fn (): string => $this->getOwnerRecord()?->currencyCode() ?? config('app.currency_default', 'SAR');

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with([
                    'boq:id,code,name',   // ✅ لإظهار اسم/كود المقايسة
                    'workItem',
                    'unit',
                ]);
            })
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('boq.code')
                    ->label('كود المقايسة')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('boq.name')
                    ->label('اسم المقايسة')
                    ->wrap()
                    ->toggleable(),

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
                    ->wrap()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money($currency)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->money($currency)
                    ->summarize([
                        Sum::make()
                            ->label('الإجمالي')
                            ->money($currency),
                    ])
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('boq_id')
            ->headerActions([])     // Read-only
            ->recordActions([])     // Read-only
            ->toolbarActions([]);   // Read-only
    }
}
