<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrxDosen extends Model
{
    use SoftDeletes, HasUuids;

    /**
     * Nama tabel di database sesuai skema asli.
     */
    protected $table = 'trx_dosen';

    /**
     * Tipe Primary Key karena menggunakan UUID (char 36) sesuai skema asli.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom-kolom yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'person_id',
        'prodi_id',
        'jenis_dosen',
        'asal_institusi',
        'nidn',
        'nuptk',
        'is_active',
        'data_tambahan',
    ];

    /**
     * Casting tipe data agar sesuai dengan skema SQL.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'data_tambahan' => 'array', // Tipe json di database dikonversi jadi array di Laravel
    ];

    /**
     * Relasi ke Data Personal.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    /**
     * Relasi ke Program Studi.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }
    public function atribusiGelar(): HasMany
    {
        return $this->hasMany(
            TrxPersonGelar::class,
            'person_id',
            'person_id'
        );
    }
    public function atribusiJabatan(): HasMany
    {
        return $this->hasMany(
            TrxPersonJabatan::class,
            'person_id',
            'person_id'
        );
    }
    public function atribusiRole(): HasMany
    {
        return $this->hasMany(
            TrxPersonRole::class,
            'person_id',
            'person_id'
        );
    }
    /**
     * (Relasi Transaksi) Dosen di Jadwal Kuliah.
     */
    public function jadwalMengajars(): HasMany
    {
        return $this->hasMany(JadwalKuliahDosen::class, 'dosen_id');
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_dosen_wali', 'dosen_id', 'kelas_id')
            ->withPivot('id', 'is_primary')
            ->withTimestamps();
    }
    public function riwayatRole(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrxPersonRole::class, 'person_id', 'person_id');
    }
    public function riwayatJabatan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrxPersonJabatan::class, 'person_id', 'person_id');
    }

    public function kelasPerwalian(): HasMany
    {
        return $this->hasMany(KelasDosenWali::class, 'dosen_id', 'id');
    }
    public function biodata()
    {
        return $this->hasOne(DosenBiodata::class, 'dosen_id');
    }

    public function riwayatPendidikan()
    {
        return $this->hasMany(DosenRiwayatPendidikan::class, 'dosen_id');
    }

    public function dokumen()
    {
        return $this->hasMany(DosenDokumen::class, 'dosen_id');
    }

    /**
     * Nama dosen langsung tanpa perlu load relasi person secara eksplisit
     * di tempat pemanggilan (tetap harus eager-load 'person' agar tidak N+1).
     */
    public function getNamaAttribute(): string
    {
        return $this->relationLoaded('person')
            ? ($this->person?->display_name ?? '(Dosen tidak ditemukan)')
            : ($this->person()->value('nama_lengkap') ?? '(Dosen tidak ditemukan)');
    }
}
