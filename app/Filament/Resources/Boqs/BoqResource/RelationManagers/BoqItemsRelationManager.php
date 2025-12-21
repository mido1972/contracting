<?php

namespace App\Filament\Resources\Boqs\BoqResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class BoqItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود المقايسة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('work_item_id')
                    ->label('بند العمل')
                    ->required()
                    ->relationship('workItem', 'name') // لو العمود مش name عدّله
                    ->searchable()
                    ->preload()
                    ->columnSpan(6),

                Select::make('unit_id')
                    ->label('الوحدة')
                    ->required()
                    ->relationship('unit', 'code')
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),

                TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->columnSpan(2),

                TextInput::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->columnSpan(2),

                TextInput::make('unit_price')
                    ->label('سعر الوحدة')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->columnSpan(2),

                TextInput::make('total_price')
                    ->label('الإجمالي')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('يتم حسابه تلقائيًا (الكمية × سعر الوحدة).')
                    ->columnSpan(2),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(12);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('ترتيب')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('workItem.name')
                    ->label('بند العمل')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة بند'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->label('حذف المحدد'),
            ]);
    }
}
