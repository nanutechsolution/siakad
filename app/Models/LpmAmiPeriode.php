<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiPeriode extends Model
{
    use HasFactory;

    protected $table = 'lpm_ami_periodes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tgl_mulai' => 'date',
            'tgl_selesai' => 'date',
        ];
    }

    public function findings(): HasMany
    {
        return $this->hasMany(LpmAmiFinding::class, 'periode_id');
    }
}