<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;

/**
 * Definisi "mahasiswa target yang layak diproses" harus SATU sumber
 * kebenaran, dipakai baik oleh Generator Tagihan (GenerateTagihanJob)
 * maupun Sinkronisasi Tagihan (SinkronisasiTagihanJob/Service). Diekstrak
 * dari logika query yang sudah ada di GenerateTagihanJob::handle() apa
 * adanya - tidak ada perubahan perilaku terhadap Generator existing.
 */
class TargetMahasiswaResolver
{
    public function resolve(array $data): Builder
    {
        $query = Mahasiswa::query()->with('person');

        if ($data['tipe_target'] === 'kolektif') {
            if (! empty($data['prodi_id'])) {
                $query->where('prodi_id', $data['prodi_id']);
            }
            if (! empty($data['angkatan_id'])) {
                $query->where('angkatan_id', $data['angkatan_id']);
            }

            // Skip mahasiswa yang punya status eksplisit selain 'A' di
            // semester ini. Mahasiswa yang BELUM punya row riwayat status
            // untuk semester ini tetap diikutkan (lihat catatan asli di
            // GenerateTagihanJob - tabel riwayat diisi manual oleh admin).
            $tahunAkademikId = $data['tahun_akademik_id'];
            $query->whereNotIn('id', function ($sub) use ($tahunAkademikId) {
                $sub->select('mahasiswa_id')
                    ->from('riwayat_status_mahasiswas')
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->where('status_kuliah', '!=', 'A');
            });
        } else {
            // Target spesifik: pilihan manual admin, tidak difilter status aktif.
            $query->where('id', $data['mahasiswa_id']);
        }

        return $query->orderBy('id');
    }
}
