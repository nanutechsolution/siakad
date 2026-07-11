<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeuanganSkemaTarif extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keuangan_skema_tarif';

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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the student batch (angkatan) for this tariff scheme.
     */
    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(RefAngkatan::class, 'angkatan_id', 'id_tahun');
    }

    /**
     * Get the study program for this tariff scheme.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the class program for this tariff scheme.
     */
    public function programKelas(): BelongsTo
    {
        return $this->belongsTo(RefProgram::class, 'program_kelas_id');
    }

    /**
     * Get the fee details associated with this scheme.
     */
    public function detailTarifs(): HasMany
    {
        return $this->hasMany(KeuanganDetailTarif::class, 'skema_tarif_id');
    }
}