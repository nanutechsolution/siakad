<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Enums\PembimbingAkademikStatus;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class PembimbingAkademikConsistencyService
{
    public function __construct(
        private readonly PembimbingAkademikResolver $resolver,
    ) {}

    /**
     * Mengecek konsistensi pembimbing akademik seorang mahasiswa.
     *
     * Status:
     * - OK
     * - BELUM_KONFIGURASI
     * - BELUM_PUNYA_KELAS
     * - KELAS_TANPA_WALI
     * - BELUM_PUNYA_WALI
     */
    public function cekMahasiswa(Mahasiswa $mahasiswa): array
    {
        $mode = $this->resolver->resolveMode($mahasiswa);

        /*
         * Tidak ada konfigurasi:
         * jangan menebak mode.
         */
        if ($mode === null) {
            return [
                'status' => 'BELUM_KONFIGURASI',
                'mode' => null,
                'kelas_id' => null,
                'kelas_nama' => null,
                'dosen_wali' => null,
                'message' => 'Konfigurasi Pembimbing Akademik belum ditentukan untuk Prodi dan Angkatan mahasiswa.',
            ];
        }

        /*
         * Mode PER_MAHASISWA:
         * kelas tidak menentukan Dosen Wali.
         */
        if ($mode === PembimbingAkademikMode::PER_MAHASISWA) {
            $wali = $this->resolver->dosenWaliAktif($mahasiswa);

            return [
                'status' => $wali
                    ? 'OK'
                    : 'BELUM_PUNYA_WALI',
                'mode' => $mode,
                'kelas_id' => null,
                'kelas_nama' => null,
                'dosen_wali' => $wali,
                'message' => $wali
                    ? 'Dosen Wali mahasiswa tersedia.'
                    : 'Mahasiswa belum memiliki Dosen Wali.',
            ];
        }

        /*
         * Mode PER_KELAS:
         * mahasiswa harus memiliki kelas aktif.
         */
        $keanggotaan = $mahasiswa->mahasiswaKelas()
            ->whereNull('tanggal_keluar')
            ->with('kelas')
            ->latest('tanggal_masuk')
            ->first();

        if (! $keanggotaan || ! $keanggotaan->kelas) {
            return [
                'status' => 'BELUM_PUNYA_KELAS',
                'mode' => $mode,
                'kelas_id' => null,
                'kelas_nama' => null,
                'dosen_wali' => null,
                'message' => 'Mahasiswa belum memiliki kelas aktif.',
            ];
        }

        $wali = $this->resolver->dosenWaliAktif($mahasiswa);

        return [
            'status' => $wali
                ? 'OK'
                : 'KELAS_TANPA_WALI',
            'mode' => $mode,
            'kelas_id' => $keanggotaan->kelas_id,
            'kelas_nama' => $keanggotaan->kelas?->nama_kelas,
            'dosen_wali' => $wali,
            'message' => $wali
                ? 'Kelas memiliki Dosen Wali aktif.'
                : 'Kelas belum memiliki Dosen Wali aktif.',
        ];
    }

    /**
     * Mengecek langsung apakah kelas membutuhkan Dosen Wali
     * berdasarkan konfigurasi Prodi + Angkatan.
     */
    public function cekKelas(Kelas $kelas): array
    {
        /*
         * Jika data kelas belum lengkap, tidak bisa menentukan konfigurasi.
         */
        if (
            $kelas->prodi_id === null ||
            $kelas->angkatan_id === null
        ) {
            return [
                'status' => 'BELUM_DAPAT_DICEK',
                'mode' => null,
                'kelas_id' => $kelas->id,
                'kelas_nama' => $kelas->nama_kelas,
                'dosen_wali' => null,
                'message' => 'Prodi atau Angkatan kelas belum tersedia.',
            ];
        }

        /*
         * Ambil konfigurasi langsung.
         *
         * Jangan menggunakan resolveMode() dengan mahasiswa dummy.
         */
        $konfigurasi = $this->resolver->getKonfigurasi(
            (int) $kelas->prodi_id,
            (int) $kelas->angkatan_id,
        );

        /*
         * Tidak ada konfigurasi:
         * jangan menganggap kelas membutuhkan wali.
         */
        if (! $konfigurasi) {
            return [
                'status' => 'BELUM_KONFIGURASI',
                'mode' => null,
                'kelas_id' => $kelas->id,
                'kelas_nama' => $kelas->nama_kelas,
                'dosen_wali' => null,
                'message' => 'Konfigurasi Pembimbing Akademik belum ditentukan.',
            ];
        }

        /*
         * PER_MAHASISWA:
         * kelas tidak memiliki wali.
         */
        if ($konfigurasi->mode !== PembimbingAkademikMode::PER_KELAS) {
            return [
                'status' => 'TIDAK_MEMBUTUHKAN_WALI_KELAS',
                'mode' => $konfigurasi->mode,
                'kelas_id' => $kelas->id,
                'kelas_nama' => $kelas->nama_kelas,
                'dosen_wali' => null,
                'message' => 'Mode Pembimbing Akademik adalah PER_MAHASISWA.',
            ];
        }

        $wali = PembimbingAkademik::query()
            ->dosenWaliAktif()
            ->where('kelas_id', $kelas->id)
            ->first();

        return [
            'status' => $wali
                ? 'OK'
                : 'KELAS_TANPA_WALI',
            'mode' => $konfigurasi->mode,
            'kelas_id' => $kelas->id,
            'kelas_nama' => $kelas->nama_kelas,
            'dosen_wali' => $wali,
            'message' => $wali
                ? 'Kelas memiliki Dosen Wali aktif.'
                : 'Kelas belum memiliki Dosen Wali aktif.',
        ];
    }

    /**
     * Query kelas yang:
     *
     * 1. memiliki konfigurasi aktif;
     * 2. mode PER_KELAS;
     * 3. belum memiliki Dosen Wali aktif.
     *
     * Jadi kelas PER_MAHASISWA tidak akan muncul sebagai warning.
     */
    public function kelasTanpaWaliQuery(): Builder
    {
        return Kelas::query()
            ->whereExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('konfigurasi_pembimbing_akademik as cfg')
                    ->whereColumn('cfg.prodi_id', 'kelas.prodi_id')
                    ->whereColumn('cfg.angkatan_id', 'kelas.angkatan_id')
                    ->where('cfg.aktif', 1)
                    ->where(
                        'cfg.mode',
                        PembimbingAkademikMode::PER_KELAS->value,
                    );
            })
            ->whereNotExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('pembimbing_akademik as pa')
                    ->whereColumn('pa.kelas_id', 'kelas.id')
                    ->where(
                        'pa.jenis',
                        PembimbingAkademikJenis::DOSEN_WALI->value,
                    )
                    ->where(
                        'pa.status',
                        PembimbingAkademikStatus::AKTIF->value,
                    )
                    ->where('pa.is_primary', 1)
                    ->whereNull('pa.deleted_at');
            });
    }

    /**
     * Mengecek kelas tujuan setelah mahasiswa ditempatkan.
     */
    public function cekKelasTujuan(int $kelasId): array
    {
        $kelas = Kelas::query()->find($kelasId);

        if (! $kelas) {
            return [
                'status' => 'KELAS_TIDAK_DITEMUKAN',
                'mode' => null,
                'kelas_id' => $kelasId,
                'kelas_nama' => null,
                'dosen_wali' => null,
                'message' => 'Kelas tujuan tidak ditemukan.',
            ];
        }

        return $this->cekKelas($kelas);
    }

    /**
     * Helper untuk mengetahui apakah kelas tujuan perlu diberi warning.
     */
    public function perluPeringatanKelas(Kelas $kelas): bool
    {
        return ($this->cekKelas($kelas)['status'] ?? null)
            === 'KELAS_TANPA_WALI';
    }
}
