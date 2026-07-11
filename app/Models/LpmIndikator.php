<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmIndikator extends Model
{
    use HasFactory;

    protected $table = 'lpm_indikators';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'is_iku' => 'boolean',
            'is_active' => 'boolean',
            'calculation_params' => 'json',
        ];
    }

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function ikuTargets(): HasMany
    {
        return $this->hasMany(LpmIkuTarget::class, 'indikator_id');
    }
}
