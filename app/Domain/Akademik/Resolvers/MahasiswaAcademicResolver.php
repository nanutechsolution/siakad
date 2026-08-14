<?php

declare(strict_types=1);

namespace App\Domain\Akademik\Resolvers;

use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\RiwayatStatusMahasiswa;
use Illuminate\Support\Collection;

class MahasiswaAcademicResolver
{
    public function __construct(
        private readonly Mahasiswa $mahasiswa,
    ) {}

    /**
     * Tahun akademik aktif saat ini.
     */
    public function tahunAkademikAktif(): ?RefTahunAkademik
    {
        return RefTahunAkademik::query()
            ->where('is_active', true)
            ->first();
    }

    /**
     * Semester pertama mahasiswa benar-benar mulai studi.
     *
     * Jangan menggunakan angkatan_id untuk business logic akademik.
     */
    public function mulaiStudi(): ?RefTahunAkademik
    {
        return $this->mahasiswa->relationLoaded('mulaiStudiTahunAkademik')
            ? $this->mahasiswa->mulaiStudiTahunAkademik
            : $this->mahasiswa->mulaiStudiTahunAkademik()->first();
    }

    /**
     * Menentukan semester berjalan berdasarkan semester mulai studi.
     *
     * Contoh:
     * mulai studi 20251
     * aktif       20251 => semester 1
     * aktif       20252 => semester 2
     * aktif       20261 => semester 3
     * aktif       20262 => semester 4
     */
    public function semesterBerjalan(
        ?RefTahunAkademik $tahunAkademik = null,
    ): ?int {
        $tahunAkademik ??= $this->tahunAkademikAktif();

        $mulaiStudi = $this->mulaiStudi();

        if (! $tahunAkademik || ! $mulaiStudi) {
            return null;
        }

        $mulai = $this->parseKodeTahun($mulaiStudi->kode_tahun);
        $sekarang = $this->parseKodeTahun($tahunAkademik->kode_tahun);

        if (! $mulai || ! $sekarang) {
            return null;
        }

        $selisihTahun = $sekarang['tahun'] - $mulai['tahun'];
        $selisihSemester = $sekarang['semester'] - $mulai['semester'];

        $semester = ($selisihTahun * 2) + $selisihSemester + 1;

        /*
         * Jika tahun akademik aktif masih sebelum mulai studi,
         * mahasiswa belum mempunyai semester berjalan.
         */
        if ($semester < 1) {
            return null;
        }

        return $semester;
    }

    /**
     * Semua riwayat status mahasiswa.
     */
    public function riwayat(): Collection
    {
        return $this->mahasiswa
            ->riwayatStatus()
            ->with('tahunAkademik')
            ->orderByDesc('tahun_akademik_id')
            ->get();
    }

    /**
     * Riwayat status akademik paling akhir.
     *
     * Ini digunakan untuk mengetahui status terakhir:
     * AKTIF, CUTI, LULUS, NON_AKTIF, dst.
     */
    public function riwayatTerakhir(): ?RiwayatStatusMahasiswa
    {
        return $this->mahasiswa
            ->riwayatStatus()
            ->with('tahunAkademik')
            ->orderByDesc('tahun_akademik_id')
            ->first();
    }

    /**
     * Riwayat terakhir yang benar-benar mempunyai capaian akademik.
     *
     * Berguna untuk menghindari kasus:
     *
     * 20251 AKTIF  IPK 3.20 SKS 20
     * 20252 CUTI   IPK 0    SKS 0
     *
     * Jika langsung mengambil record terakhir, dashboard
     * bisa salah menampilkan IPK 0.00.
     */
    public function riwayatCapaianAkademik(): ?RiwayatStatusMahasiswa
    {
        return $this->mahasiswa
            ->riwayatStatus()
            ->with('tahunAkademik')
            ->where(function ($query) {
                $query
                    ->where('sks_total', '>', 0)
                    ->orWhere('ipk', '>', 0);
            })
            ->orderByDesc('tahun_akademik_id')
            ->first();
    }

    /**
     * Status kuliah terakhir.
     */
    public function statusKuliah(): ?StatusKuliah
    {
        $riwayat = $this->riwayatTerakhir();

        if (! $riwayat) {
            return null;
        }

        return StatusKuliah::tryFrom($riwayat->status_kuliah);
    }

    /**
     * Label status kuliah.
     */
    public function statusKuliahLabel(): string
    {
        return $this->statusKuliah()?->label()
            ?? 'Belum Ada Data';
    }

    /**
     * IPK terakhir yang mempunyai capaian akademik.
     */
    public function ipk(): float
    {
        $riwayat = $this->riwayatCapaianAkademik();

        return $riwayat
            ? (float) $riwayat->ipk
            : 0.0;
    }

    /**
     * IPS terakhir yang mempunyai capaian akademik.
     */
    public function ips(): float
    {
        $riwayat = $this->riwayatCapaianAkademik();

        return $riwayat
            ? (float) $riwayat->ips
            : 0.0;
    }

    /**
     * Total SKS kumulatif terakhir.
     */
    public function sksTotal(): int
    {
        $riwayat = $this->riwayatCapaianAkademik();

        return $riwayat
            ? (int) $riwayat->sks_total
            : 0;
    }

    /**
     * SKS pada semester terakhir yang mempunyai capaian.
     */
    public function sksSemesterTerakhir(): int
    {
        $riwayat = $this->riwayatCapaianAkademik();

        return $riwayat
            ? (int) $riwayat->sks_semester
            : 0;
    }

    /**
     * Kurikulum mahasiswa.
     */
    public function sksLulusSyarat(): int
    {
        return (int) (
            $this->mahasiswa->kurikulum?->jumlah_sks_lulus
            ?? 144
        );
    }

    /**
     * Persentase progress kelulusan.
     */
    public function progressKelulusan(): float
    {
        $target = $this->sksLulusSyarat();

        if ($target <= 0) {
            return 0.0;
        }

        return min(
            100,
            round(($this->sksTotal() / $target) * 100, 1)
        );
    }

    /**
     * Warna progress kelulusan untuk UI.
     */
    public function progressKelulusanColor(): string
    {
        return match (true) {
            $this->progressKelulusan() >= 100 => 'success',
            $this->progressKelulusan() >= 75 => 'primary',
            $this->progressKelulusan() >= 40 => 'warning',
            default => 'gray',
        };
    }

    /**
     * Apakah mahasiswa sudah mempunyai riwayat akademik?
     */
    public function punyaRiwayat(): bool
    {
        return $this->mahasiswa
            ->riwayatStatus()
            ->exists();
    }

    /**
     * Apakah mahasiswa sudah mempunyai capaian akademik?
     */
    public function punyaCapaianAkademik(): bool
    {
        return $this->riwayatCapaianAkademik() !== null;
    }

    /**
     * Apakah mahasiswa sudah mulai studi?
     */
    public function sudahMulaiStudi(): bool
    {
        return $this->mulaiStudi() !== null;
    }

    /**
     * Apakah mahasiswa masih belum mempunyai data akademik?
     */
    public function belumAdaDataAkademik(): bool
    {
        return ! $this->punyaRiwayat();
    }

    /**
     * Status risiko akademik.
     *
     * Menggunakan accessor/business rule yang sudah kita buat
     * pada model Mahasiswa.
     */
    public function statusRisiko(): mixed
    {
        return $this->mahasiswa->statusRisiko;
    }

    /**
     * Apakah mahasiswa sedang cuti?
     */
    public function sedangCuti(): bool
    {
        return $this->statusKuliah() === StatusKuliah::CUTI;
    }

    /**
     * Apakah mahasiswa sudah lulus?
     */
    public function sudahLulus(): bool
    {
        return $this->statusKuliah() === StatusKuliah::LULUS;
    }

    /**
     * Apakah mahasiswa tidak boleh melakukan aktivitas akademik normal?
     */
    public function tidakAktif(): bool
    {
        return in_array(
            $this->statusKuliah(),
            [
                StatusKuliah::NON_AKTIF,
                StatusKuliah::DROP_OUT,
                StatusKuliah::KELUAR,
            ],
            true
        );
    }

    /**
     * Parsing kode tahun akademik.
     *
     * Format:
     * 20241 = Ganjil 2024/2025
     * 20242 = Genap 2024/2025
     */
    private function parseKodeTahun(?string $kode): ?array
    {
        if (! $kode || ! preg_match('/^(\d{4})([12])$/', $kode, $matches)) {
            return null;
        }

        return [
            'tahun' => (int) $matches[1],
            'semester' => (int) $matches[2],
        ];
    }
}
