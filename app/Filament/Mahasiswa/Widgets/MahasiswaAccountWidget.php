<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Models\Mahasiswa;
use App\Services\Akademik\PembimbingAkademikResolver;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MahasiswaAccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected string $view = 'filament.mahasiswa.widgets.mahasiswa-account-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $mahasiswa = Mahasiswa::with([
            'prodi',
            'person',
            'kurikulum',
        ])
            ->where('person_id', $user->person_id)
            ->first();

        if (! $mahasiswa) {
            return [
                'mahasiswa' => null,
                'user' => $user,
            ];
        }


        // Kelas aktif
        $kelasAktif = DB::table('mahasiswa_kelas')
            ->join('kelas', 'kelas.id', '=', 'mahasiswa_kelas.kelas_id')
            ->where('mahasiswa_kelas.mahasiswa_id', $mahasiswa->id)
            ->whereNull('mahasiswa_kelas.tanggal_keluar')
            ->select(
                'kelas.id',
                'kelas.nama_kelas',
                'kelas.angkatan_id'
            )
            ->first();


        /**
         * Single source of truth
         * Resolver menentukan:
         * - PER_KELAS
         * - PER_MAHASISWA
         */
        $pembimbingAkademik = app(PembimbingAkademikResolver::class)
            ->dosenWaliAktif($mahasiswa);

        $dosenWali = $pembimbingAkademik?->dosen?->person;


        return [
            'mahasiswa' => $mahasiswa,
            'user' => $user,
            'kelasAktif' => $kelasAktif,
            'dosenWali' => $dosenWali,
        ];
    }
}
