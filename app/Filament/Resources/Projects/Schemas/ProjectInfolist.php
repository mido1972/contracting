<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المشروع')
                    ->components([
                        TextEntry::make('code')
                            ->label('كود المشروع'),

                        TextEntry::make('name')
                            ->label('اسم المشروع'),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),

                        TextEntry::make('geha.Geha_Name')
                            ->label('الجهة (ERP)'),

                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('ملخص المقايسة')
                    ->visible(fn ($record) => filled($record?->boq_id))
                    ->components([
                        TextEntry::make('boq.name')
                            ->label('المقايسة'),

                        TextEntry::make('boq.total_amount')
                            ->label('إجمالي المقايسة')
                            ->money('EGP') // لو عايز نغير العملة لاحقًا
                            ->placeholder('-'),

                        TextEntry::make('boq.items_count')
                            ->label('عدد البنود')
                            ->state(function ($record) {
                                return $record->boq?->items()->count() ?? 0;
                            }),
                    ])
                    ->columns(3),
            ]);
    }
}
