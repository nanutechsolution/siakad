<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmSurveyResponden extends Model
{
    protected $fillable = [
        'kelompok_id',
        'jenis_responden',
        'person_id',
        'nama_eksternal',
        'instansi_eksternal',
        'tahun_akademik_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(LpmKuisionerKelompok::class, 'kelompok_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(LpmSurveyJawaban::class, 'responden_id');
    }

    /**
     * Nama tampilan responden: pakai nama_lengkap dari ref_person kalau
     * internal, atau nama_eksternal untuk alumni/pengguna lulusan.
     */
    public function namaTampilan(): string
    {
        return $this->person?->nama_lengkap ?? $this->nama_eksternal ?? '-';
    }
}
