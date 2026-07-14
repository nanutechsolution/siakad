<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars;

use App\Filament\Dosen\Resources\JadwalMengajars\Pages\ListJadwalMengajars;
use App\Filament\Dosen\Resources\JadwalMengajars\Pages\ViewJadwalMengajar;
use App\Filament\Dosen\Resources\JadwalMengajars\RelationManagers\SesiPerkuliahanRelationManager;
use App\Filament\Dosen\Resources\JadwalMengajars\Schemas\JadwalMengajarForm;
use App\Filament\Dosen\Resources\JadwalMengajars\Schemas\JadwalMengajarInfolist;
use App\Filament\Dosen\Resources\JadwalMengajars\Tables\JadwalMengajarsTable;
use App\Models\JadwalKuliah;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;
class JadwalMengajarResource extends Resource
{
    protected static ?string $model = JadwalKuliah::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $navigationLabel = 'Jadwal Mengajar';
    protected static ?string $modelLabel = 'Jadwal Mengajar';

    protected static ?string $pluralModelLabel = 'Jadwal Mengajar';

    protected static string|UnitEnum|null $navigationGroup = 'Perkuliahan';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $dosenId = static::currentDosenId();

        return parent::getEloquentQuery()
            ->untukDosen($dosenId)
            ->with([
                'mataKuliah',
                'kelas',
                'ruang',
                'tahunAkademik',
                'dosenPengampu' => fn($q) => $q->where('dosen_id', $dosenId),
            ])
            ->withCount([
                'sesiPerkuliahan as sesi_terlaksana_count' => fn($q) => $q->where('status_sesi', 'selesai'),
            ]);
    }

    public static function currentDosenId(): string
    {
        /** @var TrxDosen|null $dosen */
        $dosen = auth()->user()?->person?->trxDosen;

        abort_unless($dosen, 403, 'Akun Anda tidak terhubung ke data dosen. Hubungi administrator.');

        return $dosen->id;
    }

    public static function form(Schema $schema): Schema
    {
        return JadwalMengajarForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JadwalMengajarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalMengajarsTable::configure($table);
    }
    #[Override]
    public static function canView(Model $record): bool
    {
        return true;
    }

    #[Override]
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            SesiPerkuliahanRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJadwalMengajars::route('/'),
            'view' => ViewJadwalMengajar::route('/{record}'),
            'presensi-sesi' => Pages\KelolaPresensiSesi::route('/{record}/presensi/{sesiId}'),
            'rekap-kehadiran' => Pages\RekapKehadiran::route('/{record}/rekap-kehadiran'),
        ];
    }
}
