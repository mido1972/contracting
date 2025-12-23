<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Boq;
use App\Models\Sql\Geha;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المشروع')
                    ->components([
                        TextInput::make('code')
                            ->label('كود المشروع')
                            ->maxLength(50)
                            ->required(),

                        TextInput::make('name')
                            ->label('اسم المشروع')
                            ->maxLength(255)
                            ->required(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active'  => 'نشط',
                                'pending' => 'معلق',
                                'closed'  => 'مغلق',
                            ])
                            ->default('active')
                            ->required(),

                        Select::make('geha_code')
                            ->label('الجهة (من ERP)')
                            ->searchable()
                            ->preload(false) // مهم جدًا: لا preload من SQL Server
                            ->getSearchResultsUsing(function (string $search): array {
                                $search = trim($search);

                                if ($search === '') {
                                    return [];
                                }

                                return Geha::query()
                                    ->where('Geha_Name', 'like', "%{$search}%")
                                    ->orWhere('Geha_Code', 'like', "%{$search}%")
                                    ->orderBy('Geha_Name')
                                    ->limit(50)
                                    ->pluck('Geha_Name', 'Geha_Code')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                if (blank($value)) {
                                    return null;
                                }

                                return Geha::query()
                                    ->where('Geha_Code', $value)
                                    ->value('Geha_Name') ?? (string) $value;
                            })
                            ->required(),

                        Select::make('boq_id')
                            ->label('المقايسة (اختياري)')
                            ->options(fn () => Boq::query()
                                ->orderByDesc('id')
                                ->limit(200)
                                ->pluck('name', 'id')
                                ->toArray()
                            )
                            ->searchable()
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
