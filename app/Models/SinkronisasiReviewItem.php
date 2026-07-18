<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SinkronisasiReviewItem extends Model
{
    protected $fillable = [
        'sinkronisasi_batch_id',
        'tagihan_id',
        'tagihan_detail_id',
        'mahasiswa_id',
        'komponen_biaya_id',
        'nominal_existing',
        'nominal_skema_baru',
        'status',
        'keuangan_adjustment_id',
        'resolved_by',
        'resolved_at',
        'catatan_resolusi',
    ];

    protected function casts(): array
    {
        return [
            'nominal_existing' => 'decimal:2',
            'nominal_skema_baru' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SinkronisasiBatch::class, 'sinkronisasi_batch_id');
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'tagihan_id');
    }

    public function tagihanDetail(): BelongsTo
    {
        return $this->belongsTo(TagihanMahasiswaDetail::class, 'tagihan_detail_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function komponenBiaya(): BelongsTo
    {
        return $this->belongsTo(KeuanganKomponenBiaya::class, 'komponen_biaya_id');
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(KeuanganAdjustment::class, 'keuangan_adjustment_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function selisih(): float
    {
        return (float) $this->nominal_skema_baru - (float) $this->nominal_existing;
    }

    /**
     * Baris ini masih "terbuka" (belum ditindaklanjuti / diabaikan) dan
     * karenanya boleh dikonsolidasikan/di-update saat batch sinkronisasi
     * berikutnya menemukan selisih yang sama. Lihat catatan idempotency di
     * SinkronisasiTagihanJob.
     */
    public function masihTerbuka(): bool
    {
        return in_array($this->status, ['PENDING', 'IN_PROGRESS'], true);
    }
}