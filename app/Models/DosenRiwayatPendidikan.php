<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenRiwayatPendidikan extends Model
{
    protected $table = 'dosen_riwayat_pendidikan';

    protected $fillable = [
        'dosen_id', 'jenjang', 'nama_institusi', 'program_studi',
        'tahun_lulus', 'judul_tugas_akhir', 'file_ijazah_path',
    ];

    public function dosen()
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }
}