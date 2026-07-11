<?php

namespace App\Filament\Resources\Kelas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Kelas\RelationManagers\DosenWaliRelationManager;
use App\Filament\Resources\Kelas\RelationManagers\MahasiswasRelationManager;
use App\Filament\Resources\Kelas\Schemas\KelasForm;
use App\Filament\Resources\Kelas\Tables\KelasTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationLabel = 'Data Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Daftar Kelas';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::PERKULIAHAN->value;
    protected static ?int $navigationSort = 1;
    public static function form(Schema $schema): Schema
    {
        return KelasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MahasiswasRelationManager::class,
            DosenWaliRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelas::route('/'),
            'create' => CreateKelas::route('/create'),
            'edit' => EditKelas::route('/{record}/edit'),
        ];
    }
}
