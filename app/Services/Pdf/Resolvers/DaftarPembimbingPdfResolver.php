<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\DaftarPembimbingPdfData;
use App\Enums\PembimbingAkademikStatus;
use App\Models\PembimbingAkademik;

class DaftarPembimbingPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): DaftarPembimbingPdfData
    {
        $filters = $context['filters'] ?? [];

        // Tarik data menggunakan Eager Loading agar terhindar dari N+1 Query Problem
        $query = PembimbingAkademik::with([
            'dosen.person',
            'mahasiswa.person',
            'mahasiswa.prodi',
            'kelas.prodi'
        ])->where('status', PembimbingAkademikStatus::AKTIF);

        // Terapkan filter prodi
        if (! empty($filters['prodi_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('kelas', fn($k) => $k->where('prodi_id', $filters['prodi_id']))
                    ->orWhereHas('mahasiswa', fn($m) => $m->where('prodi_id', $filters['prodi_id']));
            });
        }

        // Terapkan filter angkatan
        if (! empty($filters['angkatan_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('kelas', fn($k) => $k->where('angkatan_id', $filters['angkatan_id']))
                    ->orWhereHas('mahasiswa', fn($m) => $m->where('angkatan_id', $filters['angkatan_id']));
            });
        }

        $records = $query->orderBy('kelas_id')->get();

        return new DaftarPembimbingPdfData(
            filters: $filters,
            records: $records,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
