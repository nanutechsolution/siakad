<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagihanNonReguler extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tagihan_non_regulers';

    protected $fillable = [
        'mahasiswa_id',
        'kode_transaksi',
        'deskripsi',
        'total_tagihan',
        'total_bayar',
        'status_bayar',
        'referensi_type',
        'referensi_id',
        'tenggat_waktu',
        'created_by',
    ];

    protected $casts = [
        'total_tagihan' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'tenggat_waktu' => 'date',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TagihanNonRegulerDetail::class, 'tagihan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sisa tagihan. `tagihan_non_regulers` tidak punya kolom generated
     * sisa_tagihan seperti tagihan_mahasiswas, jadi dihitung di accessor.
     */
    protected function sisaTagihan(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->total_tagihan - $this->total_bayar,
        );
    }

    public function scopeMilikMahasiswa($query, string $mahasiswaId)
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }

    public function pembayarans(): MorphMany
    {
        return $this->morphMany(
            PembayaranMahasiswa::class,
            'tagihan'
        );
    }
}
