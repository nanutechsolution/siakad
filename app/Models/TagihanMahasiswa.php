<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagihanMahasiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tagihan_mahasiswas';

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
            'total_tagihan' => 'decimal:2',
            'total_bayar' => 'decimal:2',
            'sisa_tagihan' => 'decimal:2', // Virtual column in DB
            'tenggat_waktu' => 'date',
        ];
    }

    /**
     * Get the student associated with the bill.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Get the academic year associated with the bill.
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get the user who created the bill.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the component details of the bill.
     */
    public function details(): HasMany
    {
        return $this->hasMany(TagihanMahasiswaDetail::class, 'tagihan_id');
    }

    public function pembayaran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PembayaranMahasiswa::class, 'tagihan_id');
    }
    /**
     * Get the payments made against this bill.
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(PembayaranMahasiswa::class, 'tagihan_id');
    }

    /**
     * Get the adjustments associated with this bill.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(KeuanganAdjustment::class, 'tagihan_id');
    }

    public function getSisaTagihanAttribute(): float
    {
        return max(0, $this->total_tagihan - $this->total_bayar);
    }

    public function getPersentasePembayaranAttribute(): int
    {
        if ($this->total_tagihan == 0) {
            return 0;
        }

        return min(
            100,
            round(($this->total_bayar / $this->total_tagihan) * 100)
        );
    }
}
