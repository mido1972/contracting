<?php

namespace App\Filament\Resources\WorkItemCategories;

use App\Filament\Resources\WorkItemCategories\Pages\CreateWorkItemCategory;
use App\Filament\Resources\WorkItemCategories\Pages\EditWorkItemCategory;
use App\Filament\Resources\WorkItemCategories\Pages\ListWorkItemCategories;
use App\Filament\Resources\WorkItemCategories\Schemas\WorkItemCategoryForm;
use App\Filament\Resources\WorkItemCategories\Tables\WorkItemCategoriesTable;
use App\Models\WorkItemCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkItemCategoryResource extends Resource
{
    protected static ?string $model = WorkItemCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'تصنيفات بنود الأعمال';
    }

    public static function getPluralLabel(): string
    {
        return 'تصنيفات بنود الأعمال';
    }

    public static function getModelLabel(): string
    {
        return 'تصنيف بند';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'البيانات الأساسية';
    }

    public static function form(Schema $schema): Schema
    {
        return WorkItemCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkItemCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkItemCategories::route('/'),
            'create' => CreateWorkItemCategory::route('/create'),
            'edit'   => EditWorkItemCategory::route('/{record}/edit'),
        ];
    }
}
