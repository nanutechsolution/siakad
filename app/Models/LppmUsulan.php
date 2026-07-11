<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LppmUsulan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'lppm_usulans';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'dana_diajukan' => 'decimal:2',
            'dana_disetujui' => 'decimal:2',
        ];
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(LppmSkema::class, 'skema_id');
    }

    public function ketua(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_ketua_id');
    }

    public function anggotas(): HasMany
    {
        return $this->hasMany(LppmUsulanAnggota::class, 'usulan_id');
    }
}