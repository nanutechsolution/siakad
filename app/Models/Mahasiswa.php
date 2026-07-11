<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mahasiswas';

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
            'data_tambahan' => 'json',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * Get the person data associated with the student.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    /**
     * Get the program study associated with the student.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the batch (angkatan) associated with the student.
     */
    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(RefAngkatan::class, 'angkatan_id', 'id_tahun');
    }

    /**
     * Get the program class (e.g., Reguler, Eksekutif) associated with the student.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(RefProgram::class, 'program_id');
    }

    /**
     * Get the curriculum associated with the student.
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(MasterKurikulum::class, 'kurikulum_id');
    }

    /**
     * Get the study plans (KRS) of the student.
     */
    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'mahasiswa_id');
    }

    /**
     * Get the bills associated with the student.
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Get the status history of the student.
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusMahasiswa::class, 'mahasiswa_id');
    }
    
    /**
     * Relasi ke Kelas (Inverse dari kelas->mahasiswas)
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'mahasiswa_kelas', 'mahasiswa_id', 'kelas_id')
            ->withPivot('id', 'tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }
}
