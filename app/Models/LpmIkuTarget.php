<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmIkuTarget extends Model
{
    use HasFactory;

    protected $table = 'lpm_iku_targets';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'target_nilai' => 'decimal:2',
            'capaian_nilai' => 'decimal:2',
        ];
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(LpmIndikator::class, 'indikator_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}