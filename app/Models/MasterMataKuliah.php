<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMataKuliah extends Model
{
    use SoftDeletes;

    /**
     * Nama tabel di database sesuai skema asli.
     */
    protected $table = 'master_mata_kuliahs';

    /**
     * Kolom-kolom yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prodi_id',
        'kode_mk',
        'nama_mk',
        'sks_default',
        'sks_tatap_muka',
        'sks_praktek',
        'sks_lapangan',
        'jenis_mk',
        'activity_type',
    ];

    /**
     * Casting tipe data agar sesuai dengan skema SQL.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sks_default' => 'integer',
        'sks_tatap_muka' => 'integer',
        'sks_praktek' => 'integer',
        'sks_lapangan' => 'integer',
    ];

    /**
     * Relasi ke Program Studi (RefProdi).
     * Berdasarkan constraint `master_mata_kuliahs_prodi_id_foreign`.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Relasi ke Kurikulum Mata Kuliah (Pivot kurikulum).
     * Berdasarkan constraint `kurikulum_mata_kuliah_mata_kuliah_id_foreign`.
     */
    public function kurikulumMataKuliahs(): HasMany
    {
        return $this->hasMany(KurikulumMataKuliah::class, 'mata_kuliah_id');
    }

    // public function kurikulums(): BelongsToMany
    // {
    //     return $this->belongsToMany(
    //         MasterKurikulum::class,          // Ganti dengan class model Kurikulum Anda (misal MasterKurikulum::class)
    //         'kurikulum_mata_kuliah',   // Nama tabel pivot sesuai skema Anda
    //         'mata_kuliah_id',          // Foreign key di tabel pivot ke tabel ini
    //         'kurikulum_id'             // Foreign key di tabel pivot ke tabel kurikulum
    //     );
    // }
    public function kurikulums(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            MasterKurikulum::class,
            'kurikulum_mata_kuliah',
            'mata_kuliah_id',
            'kurikulum_id'
        )->withPivot(['semester_paket', 'sks_tatap_muka', 'sks_praktek', 'sks_lapangan', 'sifat_mk']);
    }
}
