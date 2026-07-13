<?php

namespace App\Models;

use App\Enums\MetodePembayaran;
use App\Enums\StatusVerifikasiPembayaran;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PembayaranMahasiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pembayaran_mahasiswas';

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
            'nominal_bayar' => 'decimal:2',
            'tanggal_bayar' => 'datetime',
            'verified_at' => 'datetime',
            'metode_pembayaran' => MetodePembayaran::class,
            'status_verifikasi_id' => StatusVerifikasiPembayaran::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('pembayaran_keuangan');
    }
    /**
     * Get the bill this payment is applied to.
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'tagihan_id');
    }
    public function isPending(): bool
    {
        return $this->status_verifikasi_id === StatusVerifikasiPembayaran::PENDING;
    }
    /**
     * Get the verification status of this payment.
     */
    public function statusVerifikasi(): BelongsTo
    {
        return $this->belongsTo(RefStatusVerifikasiPembayaran::class, 'status_verifikasi_id');
    }
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who verified this payment.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
