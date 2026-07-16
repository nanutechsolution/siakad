<?php

namespace App\Filament\Resources\Mahasiswas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Mahasiswas\Pages\CreateMahasiswa;
use App\Filament\Resources\Mahasiswas\Pages\EditMahasiswa;
use App\Filament\Resources\Mahasiswas\Pages\ListMahasiswas;
use App\Filament\Resources\Mahasiswas\Pages\ViewMahasiswa;
use App\Filament\Resources\Mahasiswas\RelationManagers\RiwayatStatusRelationManager;
use App\Filament\Resources\Mahasiswas\Schemas\MahasiswaForm;
use App\Filament\Resources\Mahasiswas\Schemas\MahasiswaInfolist;
use App\Filament\Resources\Mahasiswas\Tables\MahasiswasTable;
use App\Models\Mahasiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static ?string $modelLabel = 'Data Mahasiswa';
    protected static ?string $pluralModelLabel = 'Data Mahasiswa';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MAHASISWA->value;
    }
    protected static ?string $recordTitleAttribute = 'nim';

    public static function getGloballySearchableAttributes(): array
    {
        return ['nim', 'person.nama_lengkap', 'person.nik'];
    }
    public static function getEloquentQuery(): Builder
    {
        return Mahasiswa::query()
            ->with([
                'person',
                'biodata',
                'prodi.fakultas',
                'program',
                'kurikulum',
                'angkatan',
                // Akademik
                'kelasAktif.kelas.dosenWaliUtama.dosen.person',
                'riwayatStatus.tahunAkademik',
                // KRS
                'krs.tahunAkademik',
                'krs.details.mataKuliah',
                // Keuangan
                'tagihan',
                'dispensasiAkademik',
                'beasiswa.beasiswa',
            ]);
    }
    public static function form(Schema $schema): Schema
    {
        return MahasiswaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MahasiswaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MahasiswasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RiwayatStatusRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMahasiswas::route('/'),
            'create' => CreateMahasiswa::route('/create'),
            'view' => ViewMahasiswa::route('/{record}'),
            'edit' => EditMahasiswa::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
