<?php

namespace App\Filament\Resources\LpmStandars;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmStandars\Pages\CreateLpmStandar;
use App\Filament\Resources\LpmStandars\Pages\EditLpmStandar;
use App\Filament\Resources\LpmStandars\Pages\ListLpmStandars;
use App\Filament\Resources\LpmStandars\RelationManagers\IndikatorsRelationManager;
use App\Filament\Resources\LpmStandars\RelationManagers\RiwayatPeningkatansRelationManager;
use App\Filament\Resources\LpmStandars\Schemas\LpmStandarForm;
use App\Filament\Resources\LpmStandars\Tables\LpmStandarsTable;
use App\Models\LpmStandar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmStandarResource extends Resource
{
    protected static ?string $model = LpmStandar::class;
    protected static ?string $navigationLabel = 'Standar Mutu';
    protected static ?string $modelLabel = 'Standar Mutu';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    public static function form(Schema $schema): Schema
    {
        return LpmStandarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmStandarsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            IndikatorsRelationManager::class,
            RiwayatPeningkatansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmStandars::route('/'),
            'create' => CreateLpmStandar::route('/create'),
            'edit' => EditLpmStandar::route('/{record}/edit'),
        ];
    }
}
