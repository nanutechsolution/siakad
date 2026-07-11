<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefAngkatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_angkatan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_tahun';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Karena id_tahun diisi manual (contoh: 2024), kita set false.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id_tahun' => 'integer',
            'batas_tahun_studi' => 'integer',
            'is_active_pmb' => 'boolean',
        ];
    }

    /**
     * Get the students for this batch/year.
     */
    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'angkatan_id', 'id_tahun');
    }
}