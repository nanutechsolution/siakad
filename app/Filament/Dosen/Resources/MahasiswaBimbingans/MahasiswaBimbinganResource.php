<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans;

use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\ListMahasiswaBimbingans;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\ViewMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas\MahasiswaBimbinganForm;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas\MahasiswaBimbinganInfolist;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables\MahasiswaBimbingansTable;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\Akademik\PembimbingAkademikResolver;
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
     * Optimasi Query (Mencegah N+1 dan memfilter data).
     * Sumber kebenaran siapa mahasiswa bimbingan dosen ini didelegasikan
     * ke PembimbingAkademikResolver — mendukung mode PER_KELAS & PER_MAHASISWA
     * tanpa perubahan kode di sini jika konfigurasi prodi berubah.
     */
    public static function getEloquentQuery(): Builder
    {
        $dosenId = Auth::user()?->person?->dosen?->id;
        $activeTaId = RefTahunAkademik::where('is_active', 1)->value('id');

        $query = parent::getEloquentQuery()
            ->with([
                'person',
                'prodi',
                'angkatan',
                'krs' => fn($q) => $q->where('tahun_akademik_id', $activeTaId),
            ]);

        if (! $dosenId) {
            return $query->whereRaw('1 = 0');
        }

        return app(PembimbingAkademikResolver::class)->scopeMahasiswaBimbingan($query, $dosenId);
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
