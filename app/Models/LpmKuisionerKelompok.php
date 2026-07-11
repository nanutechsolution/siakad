<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmKuisionerKelompok extends Model
{
    use HasFactory;

    protected $table = 'lpm_kuisioner_kelompok';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function pertanyaans(): HasMany
    {
        return $this->hasMany(LpmKuisionerPertanyaan::class, 'kelompok_id');
    }
}