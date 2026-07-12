<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefAturanSks extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 1. Validasi Logika Dasar
            if ($model->min_ips >= $model->max_ips) {
                throw new \Exception("Kesalahan Logika: Min IPS harus lebih kecil dari Max IPS.");
            }

            // 2. Validasi Tumpang Tindih (Paling Berbahaya)
            // Memastikan rentang baru tidak mengganggu rentang yang sudah ada
            $overlap = self::where('id', '!=', $model->id ?? 0)
                ->where(function ($query) use ($model) {
                    $query->whereBetween('min_ips', [$model->min_ips, $model->max_ips])
                        ->orWhereBetween('max_ips', [$model->min_ips, $model->max_ips])
                        ->orWhere(function ($q) use ($model) {
                            $q->where('min_ips', '<=', $model->min_ips)
                                ->where('max_ips', '>=', $model->max_ips);
                        });
                })->exists();

            if ($overlap) {
                throw new \Exception("Konfigurasi Gagal: Rentang IPS ini tumpang tindih dengan aturan yang sudah ada.");
            }
        });
    }
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_aturan_sks';

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
            'min_ips' => 'decimal:2',
            'max_ips' => 'decimal:2',
            'max_sks' => 'integer',
        ];
    }
}
