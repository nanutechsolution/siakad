<?php

namespace App\Services;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MahasiswaAkademikService
{
    public static function getMahasiswaAktifQuery(): Builder
    {
        $tahunAktif = DB::table('ref_tahun_akademik')
            ->where('is_active', 1)
            ->value('id');

        return Mahasiswa::query()
            // Gunakan whereHas agar Eloquent otomatis menangani join 
            // dan menghindari ambiguitas kolom
            ->whereHas('riwayatStatus', function (Builder $query) use ($tahunAktif) {
                $query->where('status_kuliah', 'A')
                      ->where('tahun_akademik_id', $tahunAktif);
            })
            // Tidak perlu lagi join manual di luar jika sudah pakai whereHas
            // Eloquent akan mengurus 'deleted_at' milik 'mahasiswas' secara otomatis
            ->whereNotNull('mahasiswas.person_id'); 
    }
}