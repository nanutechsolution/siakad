<?php

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Models\Concerns\VisibleToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKurikulum extends Model implements HasScopeStrategy
{
    use HasFactory, VisibleToUser;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_kurikulums';

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
            'tahun_mulai' => 'integer',
            'is_active' => 'boolean',
            'jumlah_sks_lulus' => 'integer',
            'jumlah_sks_wajib' => 'integer',
            'jumlah_sks_pilihan' => 'integer',
            'tgl_sk_kurikulum' => 'date',
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
     * Get the study program associated with the curriculum.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the courses associated with this curriculum.
     */
    public function kurikulumMataKuliahs(): HasMany
    {
        return $this->hasMany(KurikulumMataKuliah::class, 'kurikulum_id');
    }

    /**
     * Relasi ke Komponen Nilai Kurikulum.
     * Berdasarkan constraint `kurikulum_komponen_nilai_kurikulum_id_foreign`.
     */
    public function kurikulumKomponenNilais(): HasMany
    {
        return $this->hasMany(KurikulumKomponenNilai::class, 'kurikulum_id');
    }

    public function isModePaket(): bool
    {
        return $this->mode_krs === 'PAKET';
    }

    public function isModeBebas(): bool
    {
        return $this->mode_krs === 'BEBAS';
    }
}
