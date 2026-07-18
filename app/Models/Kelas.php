<?php

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Models\Concerns\VisibleToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kelas extends Model implements HasScopeStrategy
{
    use HasFactory, VisibleToUser;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kelas';

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
            'angkatan_id' => 'integer',
            'kapasitas' => 'integer',
        ];
    }


    public static function getSupportedScopeStrategies(): array
    {
        return [
            ScopeStrategy::GLOBAL,
            ScopeStrategy::FAKULTAS,
            ScopeStrategy::PRODI,
            ScopeStrategy::DOSEN_WALI,
            ScopeStrategy::OWNERSHIP_MAHASISWA,
        ];
    }
    public static function getFakultasScopeColumn(): ?string
    {
        return 'prodi.fakultas_id'; // dot-path -> whereHas('prodi', ...)
    }

    public static function getProdiScopeColumn(): ?string
    {
        return 'prodi_id';
    }

    public static function applyOwnershipScope(Builder $query, User $user, ScopeStrategy $strategy): Builder
    {
        return match ($strategy) {
            ScopeStrategy::OWNERSHIP_MAHASISWA => $query->where('person_id', $user->person_id),
            ScopeStrategy::DOSEN_WALI => $query->whereHas('kelas.dosenWali', function (Builder $q) use ($user) {
                $q->whereHas('dosen', fn(Builder $d) => $d->where('person_id', $user->person_id));
            }),
            default => throw new \LogicException("Mahasiswa tidak mendukung strategy {$strategy->value}"),
        };
    }
    /**
     * Get the study program associated with the class.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the program type associated with the class.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(RefProgram::class, 'program_id');
    }

    /**
     * Get the class advisory mapping.
     */
    public function kelasDosenWalis(): HasMany
    {
        return $this->hasMany(KelasDosenWali::class, 'kelas_id');
    }

    /**
     * Get the study schedules assigned to this class.
     */
    public function jadwalKuliahs(): HasMany
    {
        return $this->hasMany(JadwalKuliah::class, 'kelas_id');
    }
    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(RefAngkatan::class, 'angkatan_id', 'id_tahun');
    }

    public function mahasiswaKelas(): HasMany
    {
        return $this->hasMany(MahasiswaKelas::class, 'kelas_id', 'id');
    }

    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'mahasiswa_kelas', 'kelas_id', 'mahasiswa_id')
            ->withPivot('id', 'tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }
    public function dosens(): BelongsToMany
    {
        return $this->belongsToMany(TrxDosen::class, 'kelas_dosen_wali', 'kelas_id', 'dosen_id')
            ->withPivot('id', 'is_primary')
            ->withTimestamps();
    }
    public function dosenWali(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\TrxDosen::class, 'kelas_dosen_wali', 'kelas_id', 'dosen_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function dosenWaliUtama(): HasOne
    {
        return $this->hasOne(\App\Models\KelasDosenWali::class, 'kelas_id')
            ->where('is_primary', true);
    }
}
