<?php

namespace App\Filament\Resources\BoqProgressClaims\Schemas;

use App\Models\Boq;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class BoqProgressClaimForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات المستخلص')
                ->columns(3)
                ->schema([
                    Select::make('boq_id')
                        ->label('المقايسة')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn () => Boq::query()
                            ->orderByDesc('id')
                            ->limit(500)
                            ->pluck('code', 'id')
                            ->toArray()
                        ),

                    DatePicker::make('claim_date')
                        ->label('تاريخ المستخلص')
                        ->required()
                        ->default(now()),

                    Select::make('status')
                        ->label('الحالة')
                        ->required()
                        ->options([
                            'DRAFT'     => 'مسودة',
                            'SUBMITTED' => 'مرسل',
                            'APPROVED'  => 'معتمد',
                            'REJECTED'  => 'مرفوض',
                        ])
                        ->default('DRAFT'),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->columnSpanFull(),
                ]),

            Section::make('ملخص الحسابات')
                ->columns(3)
                ->schema([
                    TextInput::make('total_a_cumulative')
                        ->label('A الإجمالي التراكمي')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('total_b_previous')
                        ->label('B السابق')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('total_c_retention')
                        ->label('C حجز ضمان')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('total_d_deductions')
                        ->label('D خصومات / إضافات')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('vat_amount')
                        ->label('ضريبة VAT')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('net_payable')
                        ->label('الصافي')
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }
}
