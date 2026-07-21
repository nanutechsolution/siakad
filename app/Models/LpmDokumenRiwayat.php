<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmDokumenRiwayat extends Model
{
    protected $fillable = [
        'dokumen_id',
        'versi_lama',
        'versi_baru',
        'file_path',
        'changelog',
        'diubah_oleh_person_id',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(LpmDokumen::class, 'dokumen_id');
    }

    public function diubahOleh(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'diubah_oleh_person_id');
    }
}
