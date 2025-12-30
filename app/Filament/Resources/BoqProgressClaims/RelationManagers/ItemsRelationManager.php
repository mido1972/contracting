<?php

namespace App\Filament\Resources\BoqProgressClaims\RelationManagers;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

use App\Services\Boq\ProgressClaimItemUpdater;
use App\Services\Boq\ProgressClaimCalculator;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود المستخلص';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('boqItem.item_no')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('boqItem.description')
                    ->label('البند')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('qty_previous')
                    ->label('سابق')
                    ->numeric(3),

                TextColumn::make('qty_current')
                    ->label('حالي')
                    ->numeric(3),

                TextColumn::make('qty_total')
                    ->label('إجمالي')
                    ->numeric(3),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money('SAR'),

                TextColumn::make('amount_current')
                    ->label('قيمة الحالي')
                    ->money('SAR'),

                TextColumn::make('amount_total')
                    ->label('قيمة الإجمالي')
                    ->money('SAR'),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('boq_item_id', 'asc')
            ->actions([
                EditAction::make()
                    ->label('تعديل')
                    ->form([
                        TextInput::make('qty_current')
                            ->label('كمية الحالي')
                            ->required()
                            ->numeric()
                            ->rules(['min:0']),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3),
                    ])
                    ->after(function ($record, array $data): void {
                        // ✅ تحديث كمية البند والحسابات الخاصة بالبند
                        app(ProgressClaimItemUpdater::class)
                            ->updateCurrentQty($record->id, (float) ($data['qty_current'] ?? 0));

                        // ✅ إعادة حساب ملخص المستخلص بالكامل (A/B/C/D/VAT/Net)
                        $claim = $this->getOwnerRecord()->refresh();
                        app(ProgressClaimCalculator::class)->recalculate($claim);
                    }),

                DeleteAction::make()
                    ->label('حذف'),
            ]);
    }
}
