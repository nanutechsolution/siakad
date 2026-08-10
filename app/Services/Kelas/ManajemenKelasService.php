<?php

namespace App\Services\Kelas;

use App\Exceptions\ManajemenKelasException;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Semua aturan bisnis modul Manajemen Kelas: kapasitas, penempatan,
 * mutasi antar kelas, dan generate kelas otomatis per angkatan.
 *
 * Aturan bisnis utama: seorang mahasiswa hanya boleh punya SATU
 * keanggotaan kelas AKTIF (tanggal_keluar null) di waktu yang sama.
 * Pindah kelas = akhiri keanggotaan lama + buat keanggotaan baru dalam
 * satu transaksi (pola sama seperti mutasi Pembimbing Akademik).
 */
class ManajemenKelasService
{
    public function jumlahAnggotaAktif(int $kelasId): int
    {
        return MahasiswaKelas::query()->aktif()->where('kelas_id', $kelasId)->count();
    }

    /**
     * Null berarti kapasitas tidak dibatasi.
     */
    public function kapasitasTersisa(Kelas $kelas): ?int
    {
        if ($kelas->kapasitas === null) {
            return null;
        }

        return max(0, $kelas->kapasitas - $this->jumlahAnggotaAktif($kelas->id));
    }

    public function keanggotaanAktif(string $mahasiswaId): ?MahasiswaKelas
    {
        return MahasiswaKelas::query()
            ->aktif()
            ->where('mahasiswa_id', $mahasiswaId)
            ->latest('tanggal_masuk')
            ->first();
    }

    public function mahasiswaTanpaKelas(int $prodiId, $angkatanId): Builder
    {
        return Mahasiswa::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->whereNull('deleted_at')
            ->whereDoesntHave('mahasiswaKelas', fn(Builder $q) => $q->whereNull('tanggal_keluar'));
    }

    /**
     * Tempatkan satu mahasiswa ke satu kelas. Kalau mahasiswa sudah punya
     * keanggotaan aktif di kelas LAIN, otomatis dianggap mutasi (keanggotaan
     * lama diakhiri). Kalau sudah aktif di kelas yang SAMA, ditolak.
     */
    public function tempatkan(string $mahasiswaId, int $kelasId, ?string $tanggalMasuk = null): MahasiswaKelas
    {
        $kelas = Kelas::findOrFail($kelasId);
        $existing = $this->keanggotaanAktif($mahasiswaId);

        if ($existing && (int) $existing->kelas_id === $kelasId) {
            throw ManajemenKelasException::sudahDiKelasYangSama();
        }

        $sisaKapasitas = $this->kapasitasTersisa($kelas);

        if ($sisaKapasitas !== null && $sisaKapasitas <= 0) {
            throw ManajemenKelasException::kapasitasPenuh($kelas->kapasitas);
        }

        return DB::transaction(function () use ($existing, $mahasiswaId, $kelasId, $tanggalMasuk) {
            if ($existing) {
                $existing->update(['tanggal_keluar' => $tanggalMasuk ?? now()->toDateString()]);
            }

            return MahasiswaKelas::create([
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id' => $kelasId,
                'tanggal_masuk' => $tanggalMasuk ?? now()->toDateString(),
                'tanggal_keluar' => null,
            ]);
        });
    }

    /**
     * Pindahkan mahasiswa dari kelas aktifnya saat ini ke kelas lain.
     * Beda dengan tempatkan(): ini secara eksplisit MENSYARATKAN mahasiswa
     * sudah punya keanggotaan aktif (dipakai di alur "Mutasi", bukan
     * "Tempatkan" pertama kali).
     */
    public function mutasiKelas(MahasiswaKelas $keanggotaanAktif, int $kelasBaruId, ?string $tanggalMasuk = null): MahasiswaKelas
    {
        if ((int) $keanggotaanAktif->kelas_id === $kelasBaruId) {
            throw ManajemenKelasException::kelasTujuanSamaDenganAsal();
        }

        $kelasBaru = Kelas::findOrFail($kelasBaruId);
        $sisaKapasitas = $this->kapasitasTersisa($kelasBaru);

        if ($sisaKapasitas !== null && $sisaKapasitas <= 0) {
            throw ManajemenKelasException::kapasitasPenuh($kelasBaru->kapasitas);
        }

        return DB::transaction(function () use ($keanggotaanAktif, $kelasBaruId, $tanggalMasuk) {
            $tanggal = $tanggalMasuk ?? now()->toDateString();

            $keanggotaanAktif->update(['tanggal_keluar' => $tanggal]);

            return MahasiswaKelas::create([
                'mahasiswa_id' => $keanggotaanAktif->mahasiswa_id,
                'kelas_id' => $kelasBaruId,
                'tanggal_masuk' => $tanggal,
                'tanggal_keluar' => null,
            ]);
        });
    }

    public function keluarkanDariKelas(MahasiswaKelas $keanggotaan, ?string $tanggalKeluar = null): MahasiswaKelas
    {
        $keanggotaan->update(['tanggal_keluar' => $tanggalKeluar ?? now()->toDateString()]);

        return $keanggotaan;
    }

    /**
     * Generate N kelas baru untuk kombinasi prodi+program+angkatan, lalu
     * bagi rata (round-robin) seluruh mahasiswa yang belum punya kelas ke
     * kelas-kelas baru tersebut sesuai kapasitas.
     *
     * @return array{kelas: Collection<int, Kelas>, ditempatkan: int}
     */
    public function generateKelasOtomatis(
        int $prodiId,
        int $programId,
        $angkatanId,
        int $jumlahKelas,
        ?int $kapasitasPerKelas,
        string $polaNama = 'Kelas %s',
    ): array {
        return DB::transaction(function () use ($prodiId, $programId, $angkatanId, $jumlahKelas, $kapasitasPerKelas, $polaNama) {
            $abjad = range('A', 'Z');
            $kelasBaru = collect();

            for ($i = 0; $i < $jumlahKelas; $i++) {
                $label = $abjad[$i] ?? (string) ($i + 1);

                $kelasBaru->push(Kelas::create([
                    'nama_kelas' => sprintf($polaNama, $label),
                    'prodi_id' => $prodiId,
                    'program_id' => $programId,
                    'angkatan_id' => $angkatanId,
                    'kapasitas' => $kapasitasPerKelas,
                ]));
            }

            $mahasiswaIds = $this->mahasiswaTanpaKelas($prodiId, $angkatanId)->pluck('id')->values();
            $kelasIds = $kelasBaru->pluck('id')->values();

            $ditempatkan = 0;

            foreach ($mahasiswaIds as $i => $mahasiswaId) {
                $kelasId = $kelasIds[$i % $kelasIds->count()];

                MahasiswaKelas::create([
                    'mahasiswa_id' => $mahasiswaId,
                    'kelas_id' => $kelasId,
                    'tanggal_masuk' => now()->toDateString(),
                    'tanggal_keluar' => null,
                ]);

                $ditempatkan++;
            }

            return ['kelas' => $kelasBaru, 'ditempatkan' => $ditempatkan];
        });
    }

    public function totalKelas(): int
    {
        return Kelas::query()->count();
    }

    public function totalMahasiswaTanpaKelasAktif(): int
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereDoesntHave('mahasiswaKelas', fn(Builder $q) => $q->whereNull('tanggal_keluar'))
            ->count();
    }
}
