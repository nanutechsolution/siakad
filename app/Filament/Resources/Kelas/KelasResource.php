<?php

namespace App\Filament\Resources\Kelas;

use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Kelas\RelationManagers\MahasiswasRelationManager;
use App\Filament\Resources\Kelas\Schemas\KelasForm;
use App\Filament\Resources\Kelas\Tables\KelasTable;
use App\Models\Kelas;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationLabel = 'Data Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Daftar Kelas';
    /*
     * Terikat ke cluster Manajemen Kelas supaya menu "Data Kelas" hanya
     * muncul satu kali di sidebar, bukan dua (resource lama + resource
     * cluster yang sudah dihapus). NavigationGroup diwarisi dari cluster.
     */
    protected static ?string $cluster = ManajemenKelasCluster::class;
    protected static ?int $navigationSort = 1;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }
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
