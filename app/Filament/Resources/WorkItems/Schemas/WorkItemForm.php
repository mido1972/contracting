<?php

namespace App\Filament\Resources\WorkItems\Schemas;

use App\Models\Unit;
use App\Models\WorkItem;
use App\Models\WorkItemCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->required()
                    ->columnSpan(1),

                Select::make('parent_id')
                    ->label('البند الرئيسي')
                    ->relationship(
                        'parent',
                        'name',
                        fn ($query) => $query->whereNull('parent_id')
                    )
                    ->searchable()
                    ->nullable()
                    ->columnSpan(1),

                TextInput::make('code')
                    ->label('كود البند')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),

                TextInput::make('name')
                    ->label('اسم البند')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->nullable()
                    ->columnSpan(1),

                TextInput::make('default_price')
                    ->label('السعر الافتراضي')
                    ->numeric()
                    ->nullable()
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
