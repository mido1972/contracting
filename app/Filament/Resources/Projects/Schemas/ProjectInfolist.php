<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
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
                            ->label('كود المشروع')
                            ->placeholder('-'),

                        TextEntry::make('name')
                            ->label('اسم المشروع'),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),

                        TextEntry::make('geha.Geha_Name')
                            ->label('الجهة (ERP)')
                            ->placeholder('-'),

                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('ملخص المقايسات')
                    ->components([
                        TextEntry::make('boqs_count')
                            ->label('عدد المقايسات')
                            ->state(fn ($record) => $record?->boqs()?->count() ?? 0),

                        TextEntry::make('boqs_total_amount')
                            ->label('إجمالي المقايسات')
                            ->money(fn ($record) => $record?->currencyCode() ?? config('app.currency_default', 'SAR'))
                            ->state(fn ($record) => $record?->boqs()?->sum('total_amount') ?? 0),

                        TextEntry::make('boqs_draft_count')
                            ->label('مسودات')
                            ->state(fn ($record) => $record?->boqs()?->where('status', 'DRAFT')->count() ?? 0),

                        TextEntry::make('boqs_submitted_count')
                            ->label('مرسلة')
                            ->state(fn ($record) => $record?->boqs()?->where('status', 'SUBMITTED')->count() ?? 0),

                        TextEntry::make('boqs_awarded_count')
                            ->label('مُرسّاة')
                            ->state(fn ($record) => $record?->boqs()?->where('status', 'AWARDED')->count() ?? 0),

                        TextEntry::make('boqs_cancelled_count')
                            ->label('ملغاة')
                            ->state(fn ($record) => $record?->boqs()?->where('status', 'CANCELLED')->count() ?? 0),
                    ])
                    ->columns(3),
            ]);
    }
}
