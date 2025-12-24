<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\RelationManagers\BoqItemsRelationManager;
use App\Filament\Resources\Projects\RelationManagers\BoqsRelationManager;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Schemas\ProjectInfolist;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'المقاولات';
    }

    public static function getNavigationLabel(): string
    {
        return 'المشاريع';
    }

    public static function getPluralLabel(): string
    {
        return 'المشاريع';
    }

    public static function getModelLabel(): string
    {
        return 'مشروع';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'projects';
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    /**
     * ✅ فلترة المشاريع حسب الفرع/الشركة الحالية للمستخدم
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forCurrentContext()
            ->with(['geha']);
    }

    public static function getRelations(): array
    {
        return [
            BoqsRelationManager::class,     // ✅ المقايسات داخل المشروع
            BoqItemsRelationManager::class, // ✅ بنود كل المقايسات داخل المشروع (Read-only)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view'   => ViewProject::route('/{record}'),
            'edit'   => EditProject::route('/{record}/edit'),
        ];
    }
}
