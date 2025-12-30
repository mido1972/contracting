<?php

namespace App\Filament\Resources\BoqProgressClaims\Schemas;

use App\Models\Boq;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                            ->with('project:id,name') // لو العلاقة موجودة - مش هتضر
                            ->orderByDesc('id')
                            ->limit(500)
                            ->get()
                            ->mapWithKeys(function ($boq) {
                                $label = $boq->code;
                                if ($boq->relationLoaded('project') && $boq->project) {
                                    $label .= ' - ' . $boq->project->name;
                                }
                                return [$boq->id => $label];
                            })
                            ->toArray()
                        )
                        // ✅ بعد اعتماد/رفض المستخلص ممنوع تغيير المقايسة
                        ->disabled(fn ($record) => filled($record?->id) && in_array($record?->status, ['APPROVED', 'REJECTED'], true)),

                    DatePicker::make('claim_date')
                        ->label('تاريخ المستخلص')
                        ->required()
                        ->default(now())
                        ->disabled(fn ($record) => filled($record?->id) && in_array($record?->status, ['APPROVED', 'REJECTED'], true)),

                    Select::make('status')
                        ->label('الحالة')
                        ->required()
                        ->options([
                            'DRAFT'     => 'مسودة',
                            'SUBMITTED' => 'مرسل',
                            'APPROVED'  => 'معتمد',
                            'REJECTED'  => 'مرفوض',
                        ])
                        ->default('DRAFT')
                        // ✅ المستخدم مش بيغيرها يدويًا (هنخليها بأزرار لاحقًا)
                        ->disabled(fn ($record) => filled($record?->id) && in_array($record?->status, ['APPROVED', 'REJECTED'], true)),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->columnSpanFull()
                        ->disabled(fn ($record) => filled($record?->id) && in_array($record?->status, ['APPROVED', 'REJECTED'], true)),
                ]),

            Section::make('ملخص الحسابات')
                ->columns(3)
                ->schema([
                    TextInput::make('total_a_cumulative')
                        ->label('A الإجمالي التراكمي')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2)),

                    TextInput::make('total_b_previous')
                        ->label('B السابق')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2)),

                    TextInput::make('total_c_retention')
                        ->label('C حجز ضمان')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2)),

                    TextInput::make('total_d_deductions')
                        ->label('D خصومات / إضافات')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2)),

                    TextInput::make('vat_amount')
                        ->label('ضريبة VAT')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2)),

                    TextInput::make('net_payable')
                        ->label('الصافي')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2)),
                ]),
        ]);
    }
}
