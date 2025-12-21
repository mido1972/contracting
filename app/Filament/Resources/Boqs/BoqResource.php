<?php

namespace App\Filament\Resources\Boqs;

use App\Filament\Resources\Boqs\Pages\CreateBoq;
use App\Filament\Resources\Boqs\Pages\EditBoq;
use App\Filament\Resources\Boqs\Pages\ListBoqs;
use App\Filament\Resources\Boqs\Schemas\BoqForm;
use App\Filament\Resources\Boqs\Tables\BoqsTable;
use App\Models\Boq;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoqResource extends Resource
{
    protected static ?string $model = Boq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    // ترتيب الظهور في القائمة
    protected static ?int $navigationSort = 10;

    // ✅ التعريب والتنظيم (أفضل من properties لتفادي مشاكل الـ types)
    public static function getNavigationLabel(): string
    {
        return 'المقايسات';
    }

    public static function getPluralLabel(): string
    {
        return 'المقايسات';
    }

    public static function getModelLabel(): string
    {
        return 'مقايسة';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المقاولات';
    }

    public static function form(Schema $schema): Schema
    {
        return BoqForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoqsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBoqs::route('/'),
            'create' => CreateBoq::route('/create'),
            'edit' => EditBoq::route('/{record}/edit'),
        ];
    }
}
