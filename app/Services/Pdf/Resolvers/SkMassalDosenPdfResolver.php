<?php

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\SkMassalDosenPdfData;
use App\Enums\PembimbingAkademikStatus;
use App\Models\PembimbingAkademik;
use App\Models\TrxDosen;
use RuntimeException;

class SkMassalDosenPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): SkMassalDosenPdfData
    {
        $dosenId = $context['dosen_id'] ?? throw new RuntimeException('Context [dosen_id] wajib diisi.');

        // Tarik data dosen beserta relasinya
        $dosen = TrxDosen::with(['person', 'prodi'])->find($dosenId);

        if (! $dosen) {
            throw new RuntimeException("Data dosen dengan id [{$dosenId}] tidak ditemukan.");
        }

        // Tarik data bimbingan menggunakan Eloquent (menghindari raw JOIN karena relasinya kompleks/polymorphic)
        $records = PembimbingAkademik::with([
            'mahasiswa.person',
            'mahasiswa.prodi',
            'kelas.prodi'
        ])
            ->where('dosen_id', $dosenId)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->orderBy('kelas_id')
            ->get();

        return new SkMassalDosenPdfData(
            dosenId: $dosen->id,
            nidn: $dosen->nidn ?? '-',
            namaDosen: $dosen->person?->nama_lengkap ?? 'Nama Tidak Diketahui',
            prodiId: $dosen->prodi_id, // TAMBAHKAN BARIS INI
            namaProdi: $dosen->prodi?->nama_prodi ?? '-',
            records: $records,
            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }
}
