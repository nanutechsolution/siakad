<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefStatusVerifikasiPembayaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_status_verifikasi_pembayaran';

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
            'is_final' => 'boolean',
        ];
    }

    /**
     * Get the payments that have this verification status.
     */
    public function pembayaranMahasiswas(): HasMany
    {
        return $this->hasMany(PembayaranMahasiswa::class, 'status_verifikasi_id');
    }
}
