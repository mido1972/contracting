<?php

namespace App\Filament\Resources\Boqs\Tables;

use App\Models\Boq;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class BoqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // فتح صفحة التعديل عند الضغط على الصف
            ->recordUrl(fn (Boq $record) => route(
                'filament.admin.resources.boqs.edit',
                ['record' => $record]
            ))

            ->columns([
                TextColumn::make('code')
                    ->label('رقم المقايسة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم المقايسة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                // طباعة HTML
                TextColumn::make('print_link')
                    ->label('طباعة')
                    ->state(fn () => 'طباعة')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (Boq $record) => route(
                        'reports.boq.print',
                        ['boq' => $record->id]
                    ))
                    ->openUrlInNewTab(),

                // PDF (SYNC)
                TextColumn::make('pdf_link')
                    ->label('PDF')
                    ->state(fn () => 'PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Boq $record) => route(
                        'reports.boq.pdf.download',
                        ['boq' => $record->id]
                    ))
                    ->openUrlInNewTab(),

                // Excel
                TextColumn::make('excel_link')
                    ->label('Excel')
                    ->state(fn () => 'Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('warning')
                    ->url(fn (Boq $record) => route(
                        'reports.boq.excel',
                        ['boq' => $record->id]
                    ))
                    ->openUrlInNewTab(),
            ])

            // بدون Actions نهائيًا
            ->actions([]);
    }
}
