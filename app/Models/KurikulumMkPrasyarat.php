<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KurikulumMkPrasyarat extends Model
{
    /**
     * Nama tabel di database sesuai skema asli.
     */
    protected $table = 'kurikulum_mk_prasyarat';

    /**
     * Disable timestamps karena di skema asli tidak ada created_at/updated_at untuk tabel ini.
     */
    public $timestamps = false;

    /**
     * Kolom-kolom yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kurikulum_mk_id',
        'min_nilai_huruf',
        'prasyarat_kurikulum_mk_id',
        'min_nilai',
        'logic_type',
    ];

    /**
     * Casting tipe data agar sesuai dengan skema SQL.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_nilai' => 'decimal:2',
    ];

    /**
     * Relasi ke Kurikulum Mata Kuliah yang membutuhkan prasyarat.
     */
    public function kurikulumMataKuliah(): BelongsTo
    {
        return $this->belongsTo(KurikulumMataKuliah::class, 'kurikulum_mk_id');
    }

    /**
     * Relasi ke Kurikulum Mata Kuliah yang MENJADI prasyarat.
     */
    public function prasyaratMataKuliah(): BelongsTo
    {
        return $this->belongsTo(KurikulumMataKuliah::class, 'prasyarat_kurikulum_mk_id');
    }
}