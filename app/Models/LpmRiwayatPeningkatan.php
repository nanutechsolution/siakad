<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmRiwayatPeningkatan extends Model
{
    protected $fillable = [
        'standar_id',
        'versi_lama',
        'versi_baru',
        'ringkasan_perubahan',
        'dasar_peningkatan',
        'tanggal',
        'disetujui_oleh_person_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'disetujui_oleh_person_id');
    }
}
