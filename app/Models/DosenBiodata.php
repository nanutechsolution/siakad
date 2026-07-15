<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenBiodata extends Model
{
    protected $table = 'dosen_biodata';

    protected $fillable = [
        'dosen_id',
        'alamat_domisili', 'kode_pos', 'no_hp_kantor',
        'bidang_keahlian', 'minat_penelitian',
        'sinta_id', 'scopus_id', 'orcid_id', 'google_scholar_id',
        'h_index_scopus', 'h_index_scholar',
        'agama', 'status_pernikahan',
    ];

    public function dosen()
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }
}