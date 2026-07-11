<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikTranskrip extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'akademik_transkrip';

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
            'sks_diakui' => 'integer',
            'nilai_angka_final' => 'decimal:2',
            'nilai_indeks_final' => 'decimal:2',
            'is_konversi' => 'boolean',
        ];
    }

    /**
     * Get the student associated with the transcript record.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Get the course for the transcript record.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Get the KRS detail associated with the transcript record.
     */
    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }
}
