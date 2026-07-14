<?php

namespace App\Filament\Resources\NilaiMonitorings;

use App\Enums\NavigationGroup;
use App\Enums\StatusNilaiKelas;
use App\Filament\Resources\NilaiMonitorings\Pages\DetailNilaiKelas;
use App\Filament\Resources\NilaiMonitorings\Pages\ListNilaiMonitorings;
use App\Filament\Resources\NilaiMonitorings\Schemas\NilaiMonitoringForm;
use App\Filament\Resources\NilaiMonitorings\Tables\NilaiMonitoringsTable;
use App\Models\JadwalKuliah;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

class NilaiMonitoringResource extends Resource
{
    protected static ?string $model = JadwalKuliah::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'Monitoring Nilai';
    protected static ?string $modelLabel = 'Kelas Kuliah';
    protected static ?string $pluralModelLabel = 'Monitoring Nilai Akademik';
    public static function getNavigationBadge(): ?string
    {
        return JadwalKuliah::withNilaiStats()
            ->statusNilai(StatusNilaiKelas::BELUM_INPUT)
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withNilaiStats()
            ->with([
                'tahunAkademik:id,kode_tahun,nama_tahun,semester',
                'mataKuliah:id,kode_mk,nama_mk,prodi_id',
                'kelas:id,nama_kelas,prodi_id',
                'kelas.prodi:id,nama_prodi,fakultas_id',
                'kelas.prodi.fakultas:id,nama_fakultas',
                'dosenPengampu:id,person_id',
                'dosenPengampu.person:id,nama_lengkap',
            ]);
    }



    public static function form(Schema $schema): Schema
    {
        return NilaiMonitoringForm::configure($schema);
    }
    public static function table(Table $table): Table
    {
        return NilaiMonitoringsTable::configure($table);
    }

    #[Override]
    public static function canView(Model $record): bool
    {
        return parent::canView($record);
    }
    #[Override]
    public static function canDelete(Model $record): bool
    {
        return false;
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
            'index' => ListNilaiMonitorings::route('/'),
            'detail' => DetailNilaiKelas::route('/{record}/detail'),
        ];
    }
}
