<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmStandar extends Model
{
    use HasFactory;

    protected $table = 'lpm_standars';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'target_pencapaian' => 'integer',
            'versi' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function indikators(): HasMany
    {
        return $this->hasMany(LpmIndikator::class, 'standar_id');
    }
}
