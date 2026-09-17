<?php

declare(strict_types=1);

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\RekapJadwalKuliahPdfData;
use App\Services\LaporanPerkuliahan\JadwalKuliahReportService;
use RuntimeException;

class RekapJadwalKuliahPdfResolver implements PdfDataResolverInterface
{
    public function __construct(
        protected JadwalKuliahReportService $reportService,
    ) {}

    public function resolve(array $context): RekapJadwalKuliahPdfData
    {
        $tahunAkademikId = $context['tahun_akademik_id'] ?? null;

        if (! $tahunAkademikId) {
            throw new RuntimeException(
                'Context [tahun_akademik_id] wajib diisi.'
            );
        }

        $filters = [
            'tahun_akademik_id' => $tahunAkademikId,
            'fakultas_id' => $context['fakultas_id'] ?? null,
            'prodi_id' => $context['prodi_id'] ?? null,
            'dosen_id' => $context['dosen_id'] ?? null,
            'mata_kuliah_id' => $context['mata_kuliah_id'] ?? null,
            'ruang_id' => $context['ruang_id'] ?? null,
        ];

        $rows = $this->reportService->exportRows($filters);

        return new RekapJadwalKuliahPdfData(
            tahunAkademikId: (int) $tahunAkademikId,
            judulDokumen: 'Rekap Jadwal Kuliah',
            infoBaris: [],
            rows: $rows->values()->all(),
            totalJadwal: $rows->count(),
            totalKelas: $rows
                ->pluck('prodi_semester_kelas')
                ->filter()
                ->unique()
                ->count(),
            totalDosen: 0,
            totalRuang: $rows
                ->pluck('ruang')
                ->filter(fn($ruang) => filled($ruang) && $ruang !== '-')
                ->unique()
                ->count(),
            hariAktif: $rows
                ->pluck('hari')
                ->filter()
                ->unique()
                ->count(),
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
