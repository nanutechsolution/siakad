<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKurikulum extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_kurikulums';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_mulai' => 'integer',
            'is_active' => 'boolean',
            'jumlah_sks_lulus' => 'integer',
            'jumlah_sks_wajib' => 'integer',
            'jumlah_sks_pilihan' => 'integer',
            'tgl_sk_kurikulum' => 'date',
        ];
    }

    /**
     * Get the study program associated with the curriculum.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the courses associated with this curriculum.
     */
    public function kurikulumMataKuliahs(): HasMany
    {
        return $this->hasMany(KurikulumMataKuliah::class, 'kurikulum_id');
    }

    /**
     * Relasi ke Komponen Nilai Kurikulum.
     * Berdasarkan constraint `kurikulum_komponen_nilai_kurikulum_id_foreign`.
     */
    public function kurikulumKomponenNilais(): HasMany
    {
        return $this->hasMany(KurikulumKomponenNilai::class, 'kurikulum_id');
    }

    public function isModePaket(): bool
    {
        return $this->mode_krs === 'PAKET';
    }

    public function isModeBebas(): bool
    {
        return $this->mode_krs === 'BEBAS';
    }
}
