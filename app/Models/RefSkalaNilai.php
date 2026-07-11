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
}