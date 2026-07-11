<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KurikulumMataKuliah extends Model
{
    /**
     * Nama tabel di database sesuai skema asli.
     */
    protected $table = 'kurikulum_mata_kuliah';

    /**
     * Kolom-kolom yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kurikulum_id',
        'mata_kuliah_id',
        'semester_paket',
        'sks_tatap_muka',
        'sks_praktek',
        'sks_lapangan',
        'sifat_mk',
    ];

    /**
     * Casting tipe data agar sesuai dengan skema SQL.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'semester_paket' => 'integer',
        'sks_tatap_muka' => 'integer',
        'sks_praktek' => 'integer',
        'sks_lapangan' => 'integer',
    ];

    /**
     * Relasi ke Master Kurikulum.
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(MasterKurikulum::class, 'kurikulum_id');
    }

    /**
     * Relasi ke Master Mata Kuliah.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Relasi ke syarat mata kuliah (sebagai parent).
     * Mata kuliah di kurikulum ini memiliki prasyarat apa saja.
     */
    public function syaratPrasyarat(): HasMany
    {
        return $this->hasMany(KurikulumMkPrasyarat::class, 'kurikulum_mk_id');
    }

    /**
     * Relasi ke syarat mata kuliah (sebagai child/prasyaratnya).
     * Mata kuliah ini menjadi syarat untuk mata kuliah apa saja.
     */
    public function menjadiPrasyaratUntuk(): HasMany
    {
        return $this->hasMany(KurikulumMkPrasyarat::class, 'prasyarat_kurikulum_mk_id');
    }
}
