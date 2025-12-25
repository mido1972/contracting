<?php

namespace App\Filament\Resources\Boqs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('كود')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم المقايسة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                // 🖨️ Print
                Action::make('print')
                    ->label('طباعة')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('reports.boq.print', ['boq' => $record->id]), true),

                // 📄 PDF
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn ($record) => route('reports.boq.pdf', ['boq' => $record->id]), true),

                // 📊 Excel
                Action::make('excel')
                    ->label('Excel')
                    ->icon('heroicon-o-table-cells')
                    ->url(fn ($record) => route('reports.boq.excel', ['boq' => $record->id]), true),

                // ✏️ Edit
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
