<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahasiswaBiodata extends Model
{
    protected $table = 'mahasiswa_biodata';

    protected $fillable = [
        'mahasiswa_id',
        'alamat_ktp', 'alamat_domisili', 'kode_pos',
        'nama_ayah', 'nik_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
        'nama_ibu', 'nik_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
        'nama_wali', 'hubungan_wali', 'pekerjaan_wali', 'no_hp_wali',
        'agama', 'status_pernikahan', 'anak_ke', 'jumlah_saudara', 'no_kip',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}