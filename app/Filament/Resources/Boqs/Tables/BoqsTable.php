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
            // بدل EditAction: خلي الضغط على الصف يفتح صفحة التعديل مباشرة
            ->recordUrl(fn (Boq $record) => route('filament.admin.resources.boqs.edit', ['record' => $record]))

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

                // روابط مباشرة (بدون Actions) – ثابتة وسهلة
                TextColumn::make('print_link')
                    ->label('طباعة')
                    ->state(fn () => 'طباعة')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (Boq $record) => route('reports.boq.print', ['boq' => $record->id]))
                    ->openUrlInNewTab(),

                TextColumn::make('pdf_link')
                    ->label('PDF')
                    ->state(fn () => 'PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    // حسب route:list عندك: ده الاسم الصحيح لمسار /pdf
                    ->url(fn (Boq $record) => route('reports.boq.pdf.start', ['boq' => $record->id]))
                    ->openUrlInNewTab(),

                TextColumn::make('excel_link')
                    ->label('Excel')
                    ->state(fn () => 'Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('warning')
                    ->url(fn (Boq $record) => route('reports.boq.excel', ['boq' => $record->id]))
                    ->openUrlInNewTab(),
            ])

            // مهم: مفيش actions هنا خالص
            ->actions([]);
    }
}
