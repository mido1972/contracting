<?php

namespace App\Filament\Resources\BoqProgressClaims\RelationManagers;

use App\Filament\Resources\BoqProgressClaims\BoqProgressClaimResource;
use App\Services\Boq\ProgressClaimCalculator;
use App\Services\Boq\ProgressClaimItemUpdater;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود المستخلص';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('boqItem.name')
                    ->label('البند')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('qty_previous')
                    ->label('سابق')
                    ->numeric(decimalPlaces: 3),

                TextColumn::make('qty_current')
                    ->label('حالي')
                    ->numeric(decimalPlaces: 3),

                TextColumn::make('qty_total')
                    ->label('إجمالي')
                    ->numeric(decimalPlaces: 3),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money('SAR'),

                TextColumn::make('amount_current')
                    ->label('قيمة الحالي')
                    ->money('SAR'),

                TextColumn::make('amount_total')
                    ->label('قيمة الإجمالي')
                    ->money('SAR'),
            ])
            ->actions([
                EditAction::make()
                    ->label('تعديل')
                    ->modalHeading('تعديل بند المستخلص')
                    ->form([
                        TextInput::make('qty_current')
                            ->label('كمية الحالي')
                            ->numeric()
                            ->required()
                            ->rule('min:0'),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3),
                    ])
                    ->using(function ($record, array $data) {
                        // ✅ 1) تحديث البند (qty_current) وإعادة حساب سطور البند
                        app(ProgressClaimItemUpdater::class)->updateCurrentQty(
                            $record->id,
                            (float) $data['qty_current'],
                            $data['notes'] ?? null
                        );

                        // ✅ 2) إعادة حساب ملخص المستخلص بالكامل
                        $claim = $this->getOwnerRecord()->fresh();
                        app(ProgressClaimCalculator::class)->recalculate($claim);

                        // ✅ 3) Redirect لنفس صفحة Edit علشان الـ Summary يتحدث فورًا
                        return redirect(
                            BoqProgressClaimResource::getUrl('edit', [
                                'record' => $claim->getKey(),
                            ])
                        );
                    }),
            ])
            ->defaultSort('id', 'asc');
    }
}
