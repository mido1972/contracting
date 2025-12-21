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
        return $schema->components([
            Select::make('category_id')
                ->label('التصنيف')
                ->options(WorkItemCategory::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('parent_id')
                ->label('البند الرئيسي')
                ->options(
                    WorkItem::query()
                        ->whereNull('parent_id')
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->nullable(),

            TextInput::make('code')
                ->label('كود البند')
                ->required()
                ->maxLength(30)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->label('اسم البند')
                ->required()
                ->maxLength(255),

            Select::make('unit_id')
                ->label('الوحدة')
                ->options(Unit::query()->pluck('name', 'id'))
                ->searchable()
                ->nullable(),

            TextInput::make('default_price')
                ->label('السعر الافتراضي')
                ->numeric()
                ->nullable(),

            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
        ]);
    }
}
