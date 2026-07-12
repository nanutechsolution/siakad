<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefSkalaNilai extends Model
{
    use SoftDeletes;

    protected $table = 'ref_skala_nilai';

    protected $fillable = [
        'huruf',
        'bobot_indeks',
        'nilai_min',
        'nilai_max',
        'is_lulus',
    ];

    protected $casts = [
        'bobot_indeks' => 'decimal:2',
        'nilai_min' => 'decimal:2',
        'nilai_max' => 'decimal:2',
        'is_lulus' => 'boolean',
    ];
    /**
     * Mencari skala nilai (huruf) berdasarkan angka akhir.
     */
    public static function forNilai($angka)
    {
        if (! is_numeric($angka)) {
            return null;
        }
        return self::where('nilai_min', '<=', (float) $angka)
            ->where('nilai_max', '>=', (float) $angka)
            ->first();
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 1. Proteksi Range DB
            if ($model->nilai_min > 999.99 || $model->nilai_max > 999.99) {
                throw new \Exception("Nilai melebihi kapasitas sistem (Max 999.99)");
            }

            // 2. Proteksi Logika Akademik
            if ($model->nilai_min >= $model->nilai_max) {
                throw new \Exception("Logika Nilai Salah: Min harus lebih kecil dari Max");
            }
        });
    }
}
