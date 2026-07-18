<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SinkronisasiBatch extends Model
{
    protected $fillable = [
        'tahun_akademik_id',
        'mode',
        'status',
        'parameter_snapshot',
        'summary_snapshot',
        'total_mahasiswa',
        'total_ditambah',
        'total_review',
        'total_warning',
        'total_dilewati',
        'total_error',
        'error_message',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'parameter_snapshot' => 'array',
            'summary_snapshot' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewItems(): HasMany
    {
        return $this->hasMany(SinkronisasiReviewItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SinkronisasiLog::class);
    }

    public function isDryRun(): bool
    {
        return $this->mode === 'DRY_RUN';
    }
}
