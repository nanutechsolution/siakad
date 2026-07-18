<?php

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Models\Concerns\VisibleToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMataKuliah extends Model implements HasScopeStrategy
{
    use SoftDeletes, VisibleToUser;

    /**
     * Nama tabel di database sesuai skema asli.
     */
    protected $table = 'master_mata_kuliahs';

    /**
     * Kolom-kolom yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prodi_id',
        'kode_mk',
        'nama_mk',
        'sks_default',
        'sks_tatap_muka',
        'sks_praktek',
        'sks_lapangan',
        'jenis_mk',
        'activity_type',
    ];

    /**
     * Casting tipe data agar sesuai dengan skema SQL.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sks_default' => 'integer',
        'sks_tatap_muka' => 'integer',
        'sks_praktek' => 'integer',
        'sks_lapangan' => 'integer',
    ];

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
     * Relasi ke Program Studi (RefProdi).
     * Berdasarkan constraint `master_mata_kuliahs_prodi_id_foreign`.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Relasi ke Kurikulum Mata Kuliah (Pivot kurikulum).
     * Berdasarkan constraint `kurikulum_mata_kuliah_mata_kuliah_id_foreign`.
     */
    public function kurikulumMataKuliahs(): HasMany
    {
        return $this->hasMany(KurikulumMataKuliah::class, 'mata_kuliah_id');
    }

    // public function kurikulums(): BelongsToMany
    // {
    //     return $this->belongsToMany(
    //         MasterKurikulum::class,          // Ganti dengan class model Kurikulum Anda (misal MasterKurikulum::class)
    //         'kurikulum_mata_kuliah',   // Nama tabel pivot sesuai skema Anda
    //         'mata_kuliah_id',          // Foreign key di tabel pivot ke tabel ini
    //         'kurikulum_id'             // Foreign key di tabel pivot ke tabel kurikulum
    //     );
    // }
    public function kurikulums(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            MasterKurikulum::class,
            'kurikulum_mata_kuliah',
            'mata_kuliah_id',
            'kurikulum_id'
        )->withPivot(['semester_paket', 'sks_tatap_muka', 'sks_praktek', 'sks_lapangan', 'sifat_mk']);
    }
}
