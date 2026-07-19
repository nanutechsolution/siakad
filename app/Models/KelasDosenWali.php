<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KelasDosenWali extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kelas_dosen_wali';

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
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Relasi ke Kelas (Inverse dari kelas->dosens)
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_dosen_wali', 'dosen_id', 'kelas_id')
            ->withPivot('id', 'is_primary')
            ->withTimestamps();
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }
}
