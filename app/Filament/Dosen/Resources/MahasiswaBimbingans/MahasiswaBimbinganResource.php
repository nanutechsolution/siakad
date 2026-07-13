<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans;

use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\CreateMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\EditMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\ListMahasiswaBimbingans;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\ViewMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas\MahasiswaBimbinganForm;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas\MahasiswaBimbinganInfolist;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables\MahasiswaBimbingansTable;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Override;

class MahasiswaBimbinganResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $navigationLabel = 'Bimbingan Akademik';
    protected static ?string $modelLabel = 'Mahasiswa Bimbingan';
    /**
     * Otorisasi: Pastikan yang login adalah Dosen
     */
    public static function canViewAny(): bool
    {
        return Auth::user()?->person_id !== null && Auth::user()?->person?->dosen !== null;
    }
    #[Override]
    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false; // Dosen tidak membuat data mahasiswa
    }
    /**
     * Optimasi Query (Mencegah N+1 dan memfilter data)
     */
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $dosenId = $user->person?->dosen?->id;

        // Ambil ID Tahun Akademik Aktif sekali saja
        $activeTaId = RefTahunAkademik::where('is_active', 1)->value('id');

        return parent::getEloquentQuery()
            // Eager load relasi yang dibutuhkan untuk mencegah N+1 di Tabel
            ->with([
                'person',
                'prodi',
                'angkatan',
                'krs' => fn($q) => $q->where('tahun_akademik_id', $activeTaId) // Hanya ambil KRS aktif
            ])
            // Filter Mahasiswa bimbingannya saja
            ->whereHas('kelas', function ($query) use ($dosenId) {
                $query->whereHas('kelasDosenWalis', function ($q) use ($dosenId) {
                    $q->where('dosen_id', $dosenId)
                        ->where('is_primary', 1);
                });
            });
    }
    public static function form(Schema $schema): Schema
    {
        return MahasiswaBimbinganForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MahasiswaBimbinganInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MahasiswaBimbingansTable::configure($table);
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
            'index' => ListMahasiswaBimbingans::route('/'),
            'view' => ViewMahasiswaBimbingan::route('/{record}'),
        ];
    }
}
