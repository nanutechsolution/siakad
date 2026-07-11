<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefProdi extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_prodi';

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
            'is_paket' => 'boolean',
            'is_active' => 'boolean',
            'last_nim_seq' => 'integer',
        ];
    }

    /**
     * Get the faculty that owns the study program.
     */
    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(RefFakultas::class, 'fakultas_id');
    }

    /**
     * Get the students for the study program.
     */
    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'prodi_id');
    }

    /**
     * Get the curriculums for the study program.
     */
    public function kurikulums(): HasMany
    {
        return $this->hasMany(MasterKurikulum::class, 'prodi_id');
    }

    /**
     * Get the lecturers for the study program.
     */
    public function dosens(): HasMany
    {
        return $this->hasMany(TrxDosen::class, 'prodi_id');
    }

    /**
     * Get the classes for the study program.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'prodi_id');
    }
}