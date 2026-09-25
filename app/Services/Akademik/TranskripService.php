<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Models\AkademikTranskrip;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use Illuminate\Support\Facades\DB;

class TranskripService
{
    /**
     * Sinkronkan satu KRS Detail ke Transkrip Akademik.
     */
    public function sinkronkanKrsDetail(KrsDetail $detail): void
    {
        $target = $this->resolveTarget($detail);

        if ($target === null) {
            return;
        }

        DB::transaction(function () use ($detail, $target): void {
            $this->sinkronkanKrsDetailDalamTransaksi(
                $detail,
                $target['mahasiswaId'],
                $target['mataKuliahId'],
            );
        });
    }

    /**
     * @return array{mahasiswaId: string, mataKuliahId: int|string}|null
     */
    private function resolveTarget(KrsDetail $detail): ?array
    {
        if (! $detail->is_published) {
            return null;
        }

        $detail->loadMissing([
            'krs.mahasiswa',
            'jadwalKuliah.mataKuliah',
        ]);

        $mahasiswa = $detail->krs?->mahasiswa;
        $mataKuliah = $detail->jadwalKuliah?->mataKuliah;

        if (! $mahasiswa || ! $mataKuliah) {
            return null;
        }

        return [
            'mahasiswaId' => $mahasiswa->id,
            'mataKuliahId' => $mataKuliah->id,
        ];
    }

    /**
     * Sinkronkan sekumpulan detail dalam SATU transaksi.
     *
     * Versi per-baris membuka transaksi (atau savepoint bila sudah berada di
     * dalam transaksi induk) untuk tiap KRS detail — saat publish satu kelas
     * berisi ratusan mahasiswa itu ratusan commit. Satu transaksi untuk seluruh
     * batch mempertahankan perilaku all-or-nothing yang sama.
     *
     * @param  iterable<int, KrsDetail>  $details
     */
    private function sinkronkanKoleksi(iterable $details): void
    {
        $targets = [];

        foreach ($details as $detail) {
            $target = $this->resolveTarget($detail);

            if ($target === null) {
                continue;
            }

            $targets[] = [$detail, $target];
        }

        if ($targets === []) {
            return;
        }

        DB::transaction(function () use ($targets): void {
            foreach ($targets as [$detail, $target]) {
                $this->sinkronkanKrsDetailDalamTransaksi(
                    $detail,
                    $target['mahasiswaId'],
                    $target['mataKuliahId'],
                );
            }
        });
    }

    private function sinkronkanKrsDetailDalamTransaksi(
        KrsDetail $detail,
        string $mahasiswaId,
        int|string $mataKuliahId,
    ): void {
        $existing = AkademikTranskrip::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('mata_kuliah_id', $mataKuliahId)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            if (! $this->shouldReplace($existing, $detail)) {
                return;
            }

            $existing->update([
                'krs_detail_id'      => $detail->id,
                'sks_diakui'         => $detail->sks_snapshot,
                'nilai_angka_final'  => $detail->nilai_angka,
                'nilai_huruf_final'  => $detail->nilai_huruf,
                'nilai_indeks_final' => $detail->nilai_indeks,
                'is_konversi'        => false,
            ]);

            return;
        }

        AkademikTranskrip::create([
            'mahasiswa_id'       => $mahasiswaId,
            'mata_kuliah_id'     => $mataKuliahId,
            'krs_detail_id'      => $detail->id,
            'sks_diakui'         => $detail->sks_snapshot,
            'nilai_angka_final'  => $detail->nilai_angka,
            'nilai_huruf_final'  => $detail->nilai_huruf,
            'nilai_indeks_final' => $detail->nilai_indeks,
            'is_konversi'        => false,
        ]);
    }

    /**
     * Sinkronkan seluruh peserta dalam satu kelas.
     */
    public function sinkronkanKelas(JadwalKuliah $jadwal): void
    {
        $jadwal->loadMissing([
            'krsDetails.krs.mahasiswa',
            'krsDetails.jadwalKuliah.mataKuliah',
        ]);

        $this->sinkronkanKoleksi($jadwal->krsDetails);
    }

    /**
     * Sinkronkan seluruh transkrip mahasiswa.
     */
    public function sinkronkanMahasiswa(string $mahasiswaId): void
    {
        $details = KrsDetail::query()
            ->whereHas('krs', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->where('is_published', true)
            ->with([
                'krs.mahasiswa',
                'jadwalKuliah.mataKuliah',
            ])
            ->get();

        $this->sinkronkanKoleksi($details);
    }

    /**
     * Sinkronkan seluruh mahasiswa dalam sistem.
     * Berguna ketika migrasi atau perbaikan data.
     */
    public function sinkronkanSemua(): void
    {
        KrsDetail::query()
            ->where('is_published', true)
            ->with([
                'krs.mahasiswa',
                'jadwalKuliah.mataKuliah',
            ])
            ->chunkById(100, function ($details) {
            $this->sinkronkanKoleksi($details);
            });
    }

    /**
     * Menentukan apakah record transkrip lama boleh diganti.
     *
     * Saat ini menggunakan kebijakan:
     * Nilai TERAKHIR menggantikan nilai lama.
     *
     * Jika kampus menginginkan nilai terbaik,
     * cukup ubah logika method ini.
     */
    private function shouldReplace(
        AkademikTranskrip $existing,
        KrsDetail $baru,
    ): bool {
        return true;
    }
}
