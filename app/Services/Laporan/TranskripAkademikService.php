<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\DTOs\Laporan\TranskripAkademikDto;
use App\Models\AkademikTranskrip;
use App\Models\Mahasiswa;
use DateTimeImmutable;

/**
 * Service untuk Laporan Transkrip Akademik (Academic Transcript)
 * 
 * Menampilkan seluruh riwayat mata kuliah mahasiswa dengan nilai final
 */
class TranskripAkademikService extends BaseLaporanService
{
    /**
     * Ambil data Transkrip untuk mahasiswa spesifik
     * 
     * @param array $filters {
     *     @var string $mahasiswa_id Required
     *     @var int $tahun_akademik_id Optional (untuk filter periode)
     * }
     * 
     * @return array {
     *     @var TranskripAkademikDto $data
     *     @var string $filter_summary
     * }
     */
    public function getData(array $filters): array
    {
        if (empty($filters['mahasiswa_id'])) {
            throw new \InvalidArgumentException('Mahasiswa ID harus dipilih untuk transkrip akademik');
        }

        $mahasiswa = Mahasiswa::with('person', 'prodi', 'riwayatStatus')
            ->findOrFail($filters['mahasiswa_id']);

        $query = AkademikTranskrip::query()
            ->with(['mataKuliah', 'krsDetail'])
            ->where('mahasiswa_id', $mahasiswa->id);

        // Optional: filter by period
        if (!empty($filters['tahun_akademik_id'])) {
            $query->whereHas('krsDetail.krs', fn($q) => 
                $q->where('tahun_akademik_id', $filters['tahun_akademik_id'])
            );
        }

        $transkrips = $query->orderBy('created_at', 'asc')->get();

        // Hitung IPK dari transkrip
        $ipkFinal = $this->hitungIpkFinal($transkrips);
        
        // Total SKS
        $totalSksFinal = (int)$transkrips->sum('sks_diakui');

        // Transform mata kuliah details
        $mataKuliahDetails = $transkrips->map(fn(AkademikTranskrip $t) => [
            'kode_mk' => $t->mataKuliah->kode_mk,
            'nama_mk' => $t->mataKuliah->nama_mk,
            'sks' => $t->sks_diakui,
            'nilai_angka' => $t->nilai_angka_final,
            'nilai_huruf' => $t->nilai_huruf_final,
            'nilai_indeks' => $t->nilai_indeks_final,
            'is_konversi' => (bool)$t->is_konversi,
        ])->toArray();

        $dto = new TranskripAkademikDto(
            nim: $mahasiswa->nim,
            nama_mahasiswa: $mahasiswa->person->nama_lengkap,
            nama_prodi: $mahasiswa->prodi->nama_prodi,
            ipk_final: $ipkFinal,
            total_sks_final: $totalSksFinal,
            total_mata_kuliah: $transkrips->count(),
            mata_kuliah_details: $mataKuliahDetails,
            tanggal_cetak: new DateTimeImmutable(),
        );

        return [
            'data' => $dto,
            'filter_summary' => "Transkrip: {$mahasiswa->nim} - {$mahasiswa->person->nama_lengkap}",
        ];
    }

    /**
     * Hitung IPK final dari seluruh transkrip
     * 
     * IPK = Σ(nilai_indeks × sks) / Σ(sks)
     */
    private function hitungIpkFinal($transkrips): float
    {
        if ($transkrips->isEmpty()) {
            return 0.0;
        }

        $totalBobot = 0.0;
        $totalSks = 0;

        foreach ($transkrips as $t) {
            $totalBobot += $t->nilai_indeks_final * $t->sks_diakui;
            $totalSks += $t->sks_diakui;
        }

        if ($totalSks === 0) {
            return 0.0;
        }

        return round($totalBobot / $totalSks, 2);
    }

    /**
     * Ambil transkrip untuk export PDF
     */
    public function getDataForPdf(string $mahasiswaId): array
    {
        $result = $this->getData(['mahasiswa_id' => $mahasiswaId]);
        
        return [
            'mahasiswa' => [
                'nim' => $result['data']->nim,
                'nama' => $result['data']->nama_mahasiswa,
                'prodi' => $result['data']->nama_prodi,
            ],
            'transkrip' => $result['data']->mata_kuliah_details,
            'ipk' => $result['data']->ipk_final,
            'total_sks' => $result['data']->total_sks_final,
            'predikat' => $this->tentkanPredikatLulus($result['data']->ipk_final),
            'tanggal_cetak' => $result['data']->tanggal_cetak->format('d F Y'),
        ];
    }
}