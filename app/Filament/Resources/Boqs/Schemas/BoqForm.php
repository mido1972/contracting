<?php

namespace App\Filament\Resources\Boqs\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BoqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('كود المقايسة')
                ->helperText('اتركه فارغًا ليتم توليده تلقائيًا')
                ->maxLength(50)
                ->dehydrated(false), // لا يرسل قيمة فاضية للـ DB

            TextInput::make('name')
                ->label('اسم المقايسة')
                ->required()
                ->maxLength(255),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    'DRAFT' => 'مسودة',
                    'SUBMITTED' => 'مُرسلة',
                    'AWARDED' => 'تمت الترسية',
                    'CANCELLED' => 'ملغاة',
                ])
                ->default('DRAFT')
                ->required(),

            // ✅ إجمالي المقايسة (Read-only)
            Placeholder::make('total_amount_view')
                ->label('إجمالي المقايسة')
                ->content(function ($record): string {
                    $total = (float) ($record?->total_amount ?? 0);
                    return number_format($total, 2);
                }),
        ]);
    }
}
