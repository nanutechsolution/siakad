<?php

declare(strict_types=1);

namespace App\Services\Kelas;

use App\Exceptions\ManajemenKelasException;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use App\Services\Akademik\PembimbingAkademikConsistencyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ManajemenKelasService
{
    public function __construct(
        private readonly PembimbingAkademikConsistencyService $consistencyService,
    ) {}

    public function jumlahAnggotaAktif(int $kelasId): int
    {
        return MahasiswaKelas::query()
            ->aktif()
            ->where('kelas_id', $kelasId)
            ->count();
    }

    /**
     * Null berarti kapasitas tidak dibatasi.
     */
    public function kapasitasTersisa(Kelas $kelas): ?int
    {
        if ($kelas->kapasitas === null) {
            return null;
        }

        return max(
            0,
            $kelas->kapasitas - $this->jumlahAnggotaAktif($kelas->id)
        );
    }

    public function keanggotaanAktif(string $mahasiswaId): ?MahasiswaKelas
    {
        return MahasiswaKelas::query()
            ->aktif()
            ->where('mahasiswa_id', $mahasiswaId)
            ->latest('tanggal_masuk')
            ->first();
    }

    public function mahasiswaTanpaKelas(
        int $prodiId,
        $angkatanId
    ): Builder {
        return Mahasiswa::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->whereNull('deleted_at')
            ->whereDoesntHave(
                'mahasiswaKelas',
                fn(Builder $q) => $q->whereNull('tanggal_keluar')
            );
    }

    /**
     * Tempatkan satu mahasiswa ke kelas.
     *
     * Jika sudah mempunyai kelas aktif:
     * - kelas berbeda → dianggap mutasi;
     * - kelas sama → ditolak.
     *
     * Setelah berhasil, hasil consistency check kelas tujuan
     * dapat diambil melalui cekKonsistensiKelas().
     */
    public function tempatkan(
        string $mahasiswaId,
        int $kelasId,
        ?string $tanggalMasuk = null
    ): MahasiswaKelas {
        $kelas = Kelas::findOrFail($kelasId);

        $existing = $this->keanggotaanAktif($mahasiswaId);

        if (
            $existing &&
            (int) $existing->kelas_id === $kelasId
        ) {
            throw ManajemenKelasException::sudahDiKelasYangSama();
        }

        $sisaKapasitas = $this->kapasitasTersisa($kelas);

        if (
            $sisaKapasitas !== null &&
            $sisaKapasitas <= 0
        ) {
            throw ManajemenKelasException::kapasitasPenuh(
                $kelas->kapasitas
            );
        }

        return DB::transaction(function () use (
            $existing,
            $mahasiswaId,
            $kelasId,
            $tanggalMasuk
        ) {
            $tanggal = $tanggalMasuk
                ?? now()->toDateString();

            if ($existing) {
                $existing->update([
                    'tanggal_keluar' => $tanggal,
                ]);
            }

            return MahasiswaKelas::create([
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id' => $kelasId,
                'tanggal_masuk' => $tanggal,
                'tanggal_keluar' => null,
            ]);
        });
    }

    /**
     * Cek konsistensi kelas setelah penempatan/mutasi.
     */
    public function cekKonsistensiKelas(int $kelasId): array
    {
        return $this->consistencyService
            ->cekKelasTujuan($kelasId);
    }

    /**
     * Pindahkan mahasiswa dari kelas aktif ke kelas lain.
     */
    public function mutasiKelas(
        MahasiswaKelas $keanggotaanAktif,
        int $kelasBaruId,
        ?string $tanggalMasuk = null
    ): MahasiswaKelas {
        if (
            (int) $keanggotaanAktif->kelas_id === $kelasBaruId
        ) {
            throw ManajemenKelasException::kelasTujuanSamaDenganAsal();
        }

        $kelasBaru = Kelas::findOrFail($kelasBaruId);

        $sisaKapasitas = $this->kapasitasTersisa($kelasBaru);

        if (
            $sisaKapasitas !== null &&
            $sisaKapasitas <= 0
        ) {
            throw ManajemenKelasException::kapasitasPenuh(
                $kelasBaru->kapasitas
            );
        }

        return DB::transaction(function () use (
            $keanggotaanAktif,
            $kelasBaruId,
            $tanggalMasuk
        ) {
            $tanggal = $tanggalMasuk
                ?? now()->toDateString();

            $keanggotaanAktif->update([
                'tanggal_keluar' => $tanggal,
            ]);

            return MahasiswaKelas::create([
                'mahasiswa_id' => $keanggotaanAktif->mahasiswa_id,
                'kelas_id' => $kelasBaruId,
                'tanggal_masuk' => $tanggal,
                'tanggal_keluar' => null,
            ]);
        });
    }

    public function keluarkanDariKelas(
        MahasiswaKelas $keanggotaan,
        ?string $tanggalKeluar = null
    ): MahasiswaKelas {
        $keanggotaan->update([
            'tanggal_keluar' => $tanggalKeluar
                ?? now()->toDateString(),
        ]);

        return $keanggotaan;
    }

    /**
     * @param array<string, array<int, string>>|null $distribusiManual  ['A' => [mahasiswaId, ...], 'B' => [...]]
     */
    public function generateKelasOtomatis(
        int $prodiId,
        int $programId,
        $angkatanId,
        int $jumlahKelas,
        ?int $kapasitasPerKelas,
        string $polaNama = 'Kelas %s',
        ?array $distribusiManual = null,
    ): array {
        return DB::transaction(function () use (
            $prodiId,
            $programId,
            $angkatanId,
            $jumlahKelas,
            $kapasitasPerKelas,
            $polaNama,
            $distribusiManual
        ) {
            $abjad = range('A', 'Z');
            $label = fn(int $i) => $abjad[$i] ?? (string) ($i + 1);

            // Validasi kapasitas SEBELUM membuat apa pun (defense in depth,
            // preview di UI cuma bantuan visual, validasi final tetap di server)
            if ($distribusiManual && $kapasitasPerKelas !== null) {
                foreach ($distribusiManual as $labelKelas => $idsMahasiswa) {
                    if (count($idsMahasiswa) > $kapasitasPerKelas) {
                        throw ManajemenKelasException::kapasitasPenuh($kapasitasPerKelas);
                    }
                }
            }

            $kelasBaru = collect();
            $kelasPerLabel = [];

            for ($i = 0; $i < $jumlahKelas; $i++) {
                $labelKelas = $label($i);

                $kelas = Kelas::create([
                    'nama_kelas' => sprintf($polaNama, $labelKelas),
                    'prodi_id' => $prodiId,
                    'program_id' => $programId,
                    'angkatan_id' => $angkatanId,
                    'kapasitas' => $kapasitasPerKelas,
                ]);

                $kelasBaru->push($kelas);
                $kelasPerLabel[$labelKelas] = $kelas;
            }

            $ditempatkan = 0;

            if ($distribusiManual !== null) {
                // Pakai hasil edit user di preview
                foreach ($distribusiManual as $labelKelas => $idsMahasiswa) {
                    $kelasId = $kelasPerLabel[$labelKelas]->id;

                    foreach ($idsMahasiswa as $mahasiswaId) {
                        MahasiswaKelas::create([
                            'mahasiswa_id' => $mahasiswaId,
                            'kelas_id' => $kelasId,
                            'tanggal_masuk' => now()->toDateString(),
                            'tanggal_keluar' => null,
                        ]);
                        $ditempatkan++;
                    }
                }
            } else {
                // Fallback lama: round-robin otomatis
                $mahasiswaIds = $this->mahasiswaTanpaKelas($prodiId, $angkatanId)->pluck('id')->values();
                $kelasIds = $kelasBaru->pluck('id')->values();

                foreach ($mahasiswaIds as $i => $mahasiswaId) {
                    MahasiswaKelas::create([
                        'mahasiswa_id' => $mahasiswaId,
                        'kelas_id' => $kelasIds[$i % $kelasIds->count()],
                        'tanggal_masuk' => now()->toDateString(),
                        'tanggal_keluar' => null,
                    ]);
                    $ditempatkan++;
                }
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
            ->whereDoesntHave(
                'mahasiswaKelas',
                fn(Builder $q) =>
                $q->whereNull('tanggal_keluar')
            )
            ->count();
    }
}
