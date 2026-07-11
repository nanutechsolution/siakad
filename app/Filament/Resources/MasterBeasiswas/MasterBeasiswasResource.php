<?php

namespace App\Filament\Resources\MasterBeasiswas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MasterBeasiswas\Pages\CreateMasterBeasiswas;
use App\Filament\Resources\MasterBeasiswas\Pages\EditMasterBeasiswas;
use App\Filament\Resources\MasterBeasiswas\Pages\ListMasterBeasiswas;
use App\Filament\Resources\MasterBeasiswas\RelationManagers\DetailsRelationManager;
use App\Filament\Resources\MasterBeasiswas\Schemas\MasterBeasiswasForm;
use App\Filament\Resources\MasterBeasiswas\Tables\MasterBeasiswasTable;
use App\Models\KeuanganMasterBeasiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MasterBeasiswasResource extends Resource 
{
    protected static ?string $model = KeuanganMasterBeasiswa::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::BEASISWA->value;

    protected static ?string $modelLabel = 'Master Beasiswa';

    protected static ?string $pluralModelLabel = 'Master Beasiswa';

    protected static ?int $navigationSort = 3;
    public static function form(Schema $schema): Schema
    {
        return MasterBeasiswasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBeasiswasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterBeasiswas::route('/'),
            'create' => CreateMasterBeasiswas::route('/create'),
            'edit' => EditMasterBeasiswas::route('/{record}/edit'),
        ];
    }
}
