<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiPeriode extends Model
{
    protected $table = 'lpm_ami_periodes';

    protected $fillable = [
        'nama_periode',
        'tahun',
        'tgl_mulai',
        'tgl_selesai',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'is_active',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * NOTE: schema Anda punya dua pasang kolom tanggal (tanggal_mulai/
     * tanggal_selesai yang nullable, dan tgl_mulai/tgl_selesai yang NOT
     * NULL) plus dua penanda status (is_active boolean & status enum
     * DRAFT/ON-GOING/FINISHED) — kemungkinan sisa evolusi migration lama.
     * Model ini expose semuanya; silakan konsolidasi ke satu pasang saja
     * di project Anda kalau salah satunya sudah tidak dipakai.
     */

    public function programs(): HasMany
    {
        return $this->hasMany(LpmAmiProgram::class, 'periode_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(LpmAmiFinding::class, 'periode_id');
    }
}