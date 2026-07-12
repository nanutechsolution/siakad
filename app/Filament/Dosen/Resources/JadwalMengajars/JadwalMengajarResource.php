<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars;

use App\Filament\Dosen\Resources\JadwalMengajars\Pages\CreateJadwalMengajar;
use App\Filament\Dosen\Resources\JadwalMengajars\Pages\EditJadwalMengajar;
use App\Filament\Dosen\Resources\JadwalMengajars\Pages\KelolaPresensiSesi;
use App\Filament\Dosen\Resources\JadwalMengajars\Pages\ListJadwalMengajars;
use App\Filament\Dosen\Resources\JadwalMengajars\Pages\ViewJadwalMengajar;
use App\Filament\Dosen\Resources\JadwalMengajars\RelationManagers\SesiPerkuliahanRelationManager;
use App\Filament\Dosen\Resources\JadwalMengajars\Schemas\JadwalMengajarForm;
use App\Filament\Dosen\Resources\JadwalMengajars\Schemas\JadwalMengajarInfolist;
use App\Filament\Dosen\Resources\JadwalMengajars\Tables\JadwalMengajarsTable;
use App\Models\JadwalKuliah;
use App\Models\JadwalMengajar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class JadwalMengajarResource extends Resource
{
    protected static ?string $model = JadwalKuliah::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $navigationLabel = 'Jadwal Mengajar';
    protected static ?string $modelLabel = 'Jadwal Kuliah';
    protected static ?string $pluralModelLabel = 'Jadwal Mengajar';
    public static function form(Schema $schema): Schema
    {
        return JadwalMengajarForm::configure($schema);
    }

    /**
     * Override default behavior Filament di ViewRecord Page.
     * Paksa Relation Manager ini agar tetap bisa melakukan aksi Create/Edit/Delete.
     */
    public function isReadOnly(): bool
    {
        return false;
    }
    public static function canCreate(): bool
    {
        return true;
    }
    /**
     * Izinkan dosen melihat daftar jadwal (jika belum ada)
     */
    public static function canViewAny(): bool
    {
        return true;
    }

    /**
     * Izinkan dosen melihat detail record (Wajib agar ViewAction muncul)
     */
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Karena ini Dosen Panel, asumsikan dosen boleh melihat detail jadwalnya sendiri
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        $dosenId = auth()->user()->person?->dosen?->id;

        // Hanya tampilkan jadwal untuk dosen yang sedang login
        return parent::getEloquentQuery()
            ->whereHas('dosen', function (Builder $query) use ($dosenId) {
                $query->where('dosen_id', $dosenId);
            });
    }
    public static function infolist(Schema $schema): Schema
    {
        return JadwalMengajarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalMengajarsTable::configure($table);
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
            'create' => CreateJadwalMengajar::route('/create'),
            'view' => ViewJadwalMengajar::route('/{record}'),
            'edit' => EditJadwalMengajar::route('/{record}/edit'),
            'presensi' => KelolaPresensiSesi::route('/{record}/sesi/{sesiId}/presensi'),
        ];
    }
}
