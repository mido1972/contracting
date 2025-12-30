<?php

namespace App\Filament\Resources\BoqProgressClaims;

use App\Filament\Resources\BoqProgressClaims\Pages\CreateBoqProgressClaim;
use App\Filament\Resources\BoqProgressClaims\Pages\EditBoqProgressClaim;
use App\Filament\Resources\BoqProgressClaims\Pages\ListBoqProgressClaims;
use App\Filament\Resources\BoqProgressClaims\Schemas\BoqProgressClaimForm;
use App\Filament\Resources\BoqProgressClaims\Tables\BoqProgressClaimsTable;
use App\Filament\Resources\BoqProgressClaims\RelationManagers\ItemsRelationManager;
use App\Models\BoqProgressClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoqProgressClaimResource extends Resource
{
    protected static ?string $model = BoqProgressClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $navigationLabel = 'المستخلصات';
    protected static ?string $modelLabel = 'مستخلص';
    protected static ?string $pluralModelLabel = 'المستخلصات';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return BoqProgressClaimForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoqProgressClaimsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBoqProgressClaims::route('/'),
            'create' => CreateBoqProgressClaim::route('/create'),
            'edit'   => EditBoqProgressClaim::route('/{record}/edit'),
        ];
    }
}
