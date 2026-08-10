<?php

namespace App\Services\Kelas;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Models\Kelas;
use App\Models\KonfigurasiPembimbingAkademik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Single query entry point untuk dashboard/monitoring Modul Kelas.
 * Filament Widgets TIDAK boleh query Eloquent langsung — konsisten
 * dengan pola NilaiAkademikService.
 *
 * CATATAN: tabel `kelas` belum punya kolom `status` (lifecycle
 * DRAFT/AKTIF/DITUTUP masih pending migration). Semua kelas yang ada
 * di tabel dianggap berlaku apa adanya.
 */
class KelasDashboardService
{
    /**
     * Ambil kelas + hitung mahasiswa aktif (tanggal_keluar null) sekaligus,
     * dipakai bareng oleh beberapa method di bawah supaya tidak query ulang-ulang.
     */
    protected function kelasWithMahasiswaAktifCount(): Collection
    {
        return Kelas::query()
            ->withCount([
                'mahasiswaKelas as mahasiswa_aktif_count' => fn(Builder $q) => $q->whereNull('tanggal_keluar'),
            ])
            ->get(['id', 'nama_kelas', 'prodi_id', 'program_id', 'angkatan_id', 'kapasitas']);
    }

    public function overviewStats(): array
    {
        $kelas = $this->kelasWithMahasiswaAktifCount();

        $totalKapasitas = (int) $kelas->sum('kapasitas');
        $totalTerisi = (int) $kelas->sum('mahasiswa_aktif_count');

        $okupansi = $totalKapasitas > 0
            ? round(($totalTerisi / $totalKapasitas) * 100, 1)
            : 0.0;

        return [
            'total_kelas' => $kelas->count(),
            'total_prodi' => $kelas->pluck('prodi_id')->unique()->count(),
            'total_program' => $kelas->pluck('program_id')->unique()->count(),
            'total_kapasitas' => $totalKapasitas,
            'total_terisi' => $totalTerisi,
            'okupansi_persen' => $okupansi,
        ];
    }

    /**
     * Peta mode konfigurasi wali per "prodi_id-angkatan_id" untuk dipakai
     * saat menentukan apakah sebuah kelas "tanpa wali" itu relevan atau tidak.
     */
    protected function konfigurasiMap(): Collection
    {
        return KonfigurasiPembimbingAkademik::query()
            ->where('aktif', true)
            ->get(['prodi_id', 'angkatan_id', 'mode'])
            ->keyBy(fn($k) => "{$k->prodi_id}-{$k->angkatan_id}");
    }

    /**
     * Set kelas_id yang punya Dosen Wali AKTIF ter-assign langsung (mode PER_KELAS).
     */
    protected function kelasIdDenganWaliAktif(): array
    {
        return \App\Models\PembimbingAkademik::query()
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->whereNotNull('kelas_id')
            ->pluck('kelas_id')
            ->unique()
            ->all();
    }

    /**
     * Indikator kesehatan data kelas untuk ditindaklanjuti BAAK/Kaprodi.
     */
    public function healthCheck(): array
    {
        $kelas = $this->kelasWithMahasiswaAktifCount();
        $konfigurasiMap = $this->konfigurasiMap();
        $kelasIdDenganWali = $this->kelasIdDenganWaliAktif();

        $tanpaWali = 0;
        $tanpaKonfigurasi = 0;

        foreach ($kelas as $k) {
            $konfig = $konfigurasiMap->get("{$k->prodi_id}-{$k->angkatan_id}");

            if (! $konfig) {
                $tanpaKonfigurasi++;
                continue;
            }

            if ($konfig->mode->value === 'PER_KELAS' && ! in_array($k->id, $kelasIdDenganWali, true)) {
                $tanpaWali++;
            }
        }

        return [
            'tanpa_wali' => $tanpaWali,
            'tanpa_konfigurasi' => $tanpaKonfigurasi,
            'kelas_kosong' => $kelas->where('mahasiswa_aktif_count', 0)->count(),
            'over_capacity' => $kelas
                ->filter(fn($k) => $k->kapasitas !== null && $k->mahasiswa_aktif_count > $k->kapasitas)
                ->count(),
        ];
    }

    /**
     * Distribusi jumlah kelas per Program Studi (untuk chart).
     */
    public function distribusiPerProdi(): Collection
    {
        return Kelas::query()
            ->selectRaw('prodi_id, count(*) as total')
            ->with('prodi:id,nama_prodi')
            ->groupBy('prodi_id')
            ->get();
    }

    /**
     * ID kelas yang bermasalah: kosong, melebihi kapasitas, atau (mode PER_KELAS) tanpa wali.
     */
    protected function problemKelasIds(): array
    {
        $kelas = $this->kelasWithMahasiswaAktifCount();
        $konfigurasiMap = $this->konfigurasiMap();
        $kelasIdDenganWali = $this->kelasIdDenganWaliAktif();

        return $kelas->filter(function ($k) use ($konfigurasiMap, $kelasIdDenganWali) {
            $kosong = $k->mahasiswa_aktif_count === 0;
            $overKapasitas = $k->kapasitas !== null && $k->mahasiswa_aktif_count > $k->kapasitas;

            $konfig = $konfigurasiMap->get("{$k->prodi_id}-{$k->angkatan_id}");
            $tanpaWali = $konfig
                && $konfig->mode->value === 'PER_KELAS'
                && ! in_array($k->id, $kelasIdDenganWali, true);

            return $kosong || $overKapasitas || $tanpaWali;
        })->pluck('id')->all();
    }

    public function problemQuery(): Builder
    {
        return Kelas::query()
            ->withCount([
                'mahasiswaKelas as mahasiswa_aktif_count' => fn(Builder $q) => $q->whereNull('tanggal_keluar'),
            ])
            ->whereIn('id', $this->problemKelasIds());
    }
}