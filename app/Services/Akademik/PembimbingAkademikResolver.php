<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Enums\PembimbingAkademikStatus;
use App\Models\KonfigurasiPembimbingAkademik;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth untuk menentukan Dosen Wali (Pembimbing Akademik jenis DOSEN_WALI).
 *
 * Mode penentuan (PER_KELAS / PER_MAHASISWA) bersifat dinamis per kombinasi
 * (prodi_id, angkatan_id), dibaca dari `konfigurasi_pembimbing_akademik`.
 *
 * Semua modul (Panel Dosen, Approval KRS, Dashboard, Monitoring, dll.) WAJIB
 * menggunakan service ini, bukan query manual ke `pembimbing_akademik`
 * atau tabel legacy `kelas_dosen_wali` / `mahasiswa_dosen_wali`.
 */
class PembimbingAkademikResolver
{

    /** Cache request-level agar tidak query berulang untuk kombinasi prodi+angkatan yang sama. */
    private array $konfigurasiCache = [];

    public function getKonfigurasi(int $prodiId, int $angkatanId): ?KonfigurasiPembimbingAkademik
    {
        $cacheKey = "{$prodiId}:{$angkatanId}";

        if (! array_key_exists($cacheKey, $this->konfigurasiCache)) {
            $this->konfigurasiCache[$cacheKey] = KonfigurasiPembimbingAkademik::query()
                ->where('prodi_id', $prodiId)
                ->where('angkatan_id', $angkatanId)
                ->where('aktif', true)
                ->first();
        }

        return $this->konfigurasiCache[$cacheKey];
    }

    public function resolveMode(Mahasiswa $mahasiswa): PembimbingAkademikMode
    {
        $konfig = $this->getKonfigurasi((int) $mahasiswa->prodi_id, (int) $mahasiswa->angkatan_id);

        if (! $konfig) {
            Log::warning('Konfigurasi pembimbing akademik belum diset, fallback ke PER_KELAS.', [
                'mahasiswa_id' => $mahasiswa->id,
                'prodi_id' => $mahasiswa->prodi_id,
                'angkatan_id' => $mahasiswa->angkatan_id,
            ]);

            return PembimbingAkademikMode::PER_KELAS;
        }

        return $konfig->mode;
    }

    /**
     * Dosen Wali aktif untuk satu mahasiswa (single source of truth).
     */
    public function dosenWaliAktif(Mahasiswa $mahasiswa): ?PembimbingAkademik
    {
        $mode = $this->resolveMode($mahasiswa);

        if ($mode === PembimbingAkademikMode::PER_MAHASISWA) {
            return PembimbingAkademik::query()
                ->dosenWaliAktif()
                ->with([
                    'dosen.person.gelars',
                ])
                ->where('mahasiswa_id', $mahasiswa->id)
                ->first();
        }

        // Mode PER_KELAS: cari kelas aktif mahasiswa (belum keluar), lalu dosen wali kelas tsb.
        $kelasId = DB::table('mahasiswa_kelas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereNull('tanggal_keluar')
            ->orderByDesc('tanggal_masuk')
            ->value('kelas_id');

        if (! $kelasId) {
            return null;
        }

        return PembimbingAkademik::query()
            ->dosenWaliAktif()
            ->with([
                'dosen.person.gelars',
            ])
            ->where('kelas_id', $kelasId)
            ->first();
    }

    /**
     * Menerapkan filter "mahasiswa bimbingan dosen X" ke Builder Mahasiswa,
     * dengan menghormati mode PER_KELAS maupun PER_MAHASISWA sekaligus
     * (satu dosen bisa membimbing lewat dua mode berbeda di prodi/angkatan berbeda).
     */
    public function scopeMahasiswaBimbingan(Builder $query, string $dosenId): Builder
    {
        return $query->where(function (Builder $q) use ($dosenId) {
            $q->where(function (Builder $perMahasiswa) use ($dosenId) {
                $perMahasiswa->whereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('konfigurasi_pembimbing_akademik as cfg')
                        ->whereColumn('cfg.prodi_id', 'mahasiswas.prodi_id')
                        ->whereColumn('cfg.angkatan_id', 'mahasiswas.angkatan_id')
                        ->where('cfg.aktif', 1)
                        ->where('cfg.mode', PembimbingAkademikMode::PER_MAHASISWA->value);
                })->whereExists(function ($sub) use ($dosenId) {
                    $sub->selectRaw('1')
                        ->from('pembimbing_akademik as pa')
                        ->whereColumn('pa.mahasiswa_id', 'mahasiswas.id')
                        ->where(
                            'pa.jenis',
                            PembimbingAkademikJenis::DOSEN_WALI->value
                        )

                        ->where('pa.is_primary', 1)
                        ->where(
                            'pa.status',
                            PembimbingAkademikStatus::AKTIF->value
                        )
                        ->where('pa.dosen_id', $dosenId)
                        ->whereNull('pa.deleted_at');
                });
            })->orWhere(function (Builder $perKelas) use ($dosenId) {
                $perKelas->where(function (Builder $modeCheck) {
                    // Mode PER_KELAS eksplisit ATAU belum dikonfigurasi (fallback default)
                    $modeCheck->whereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('konfigurasi_pembimbing_akademik as cfg')
                            ->whereColumn('cfg.prodi_id', 'mahasiswas.prodi_id')
                            ->whereColumn('cfg.angkatan_id', 'mahasiswas.angkatan_id')
                            ->where('cfg.aktif', 1)
                            ->where('cfg.mode', PembimbingAkademikMode::PER_KELAS->value);
                    })->orWhereNotExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('konfigurasi_pembimbing_akademik as cfg')
                            ->whereColumn('cfg.prodi_id', 'mahasiswas.prodi_id')
                            ->whereColumn('cfg.angkatan_id', 'mahasiswas.angkatan_id')
                            ->where('cfg.aktif', 1);
                    });
                })->whereExists(function ($sub) use ($dosenId) {
                    $sub->selectRaw('1')
                        ->from('mahasiswa_kelas as mk')
                        ->join('pembimbing_akademik as pa', 'pa.kelas_id', '=', 'mk.kelas_id')
                        ->whereColumn('mk.mahasiswa_id', 'mahasiswas.id')
                        ->whereNull('mk.tanggal_keluar')
                        ->where(
                            'pa.jenis',
                            PembimbingAkademikJenis::DOSEN_WALI->value
                        )
                        ->where(
                            'pa.status',
                            PembimbingAkademikStatus::AKTIF->value
                        )
                        ->where('pa.is_primary', 1)
                        ->where('pa.dosen_id', $dosenId)
                        ->whereNull('pa.deleted_at');
                });
            });
        });
    }


    public function jumlahMahasiswaBimbingan(string $dosenId): int
    {
        return $this->scopeMahasiswaBimbingan(
            Mahasiswa::query(),
            $dosenId
        )->distinct('mahasiswas.id')
            ->count('mahasiswas.id');
    }
}
