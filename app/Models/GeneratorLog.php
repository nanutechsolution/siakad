<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratorLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'generator_batch_id',
        'mahasiswa_id',
        'status',
        'total_tagihan',
        'pesan',
    ];

    protected function casts(): array
    {
        return [
            'total_tagihan' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(GeneratorBatch::class, 'generator_batch_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
