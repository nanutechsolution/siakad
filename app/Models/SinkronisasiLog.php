<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SinkronisasiLog extends Model
{
    // Append-only: tidak ada updated_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'sinkronisasi_batch_id',
        'mahasiswa_id',
        'status',
        'jumlah_ditambah',
        'jumlah_review',
        'jumlah_warning',
        'pesan',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SinkronisasiBatch::class, 'sinkronisasi_batch_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
