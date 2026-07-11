<?php

namespace App\Filament\Resources\Pegawais;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Pegawais\Pages\CreatePegawai;
use App\Filament\Resources\Pegawais\Pages\EditPegawai;
use App\Filament\Resources\Pegawais\Pages\ListPegawais;
use App\Filament\Resources\Pegawais\Schemas\PegawaiForm;
use App\Filament\Resources\Pegawais\Tables\PegawaisTable;
use App\Models\TrxPegawai;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PegawaiResource extends Resource
{
    protected static ?string $model = TrxPegawai::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;
    protected static ?string $modelLabel = 'Data Pegawai';
    protected static ?string $pluralModelLabel = 'Data Pegawai';
    protected static ?int $navigationSort = 4;
    public static function form(Schema $schema): Schema
    {
        return PegawaiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PegawaisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RiwayatJabatanRelationManager::class,
            RelationManagers\AtribusiGelarRelationManager::class,
            RelationManagers\RiwayatRoleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPegawais::route('/'),
            'create' => CreatePegawai::route('/create'),
            'edit' => EditPegawai::route('/{record}/edit'),
        ];
    }
}
