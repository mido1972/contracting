<?php

namespace App\Filament\Resources\WorkItemCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WorkItemCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('الكود')
                ->required()
                ->maxLength(20)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->label('اسم التصنيف')
                ->required()
                ->maxLength(255),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
        ]);
    }
}
