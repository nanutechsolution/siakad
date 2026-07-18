<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Enums\KrsStatusEnum;
use App\Models\Concerns\VisibleToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Krs extends Model implements HasScopeStrategy
{
    use HasUuids, LogsActivity, VisibleToUser;

    protected $table = 'krs';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'kelas_id',
        'tgl_krs',
        'status_krs',
        'is_paket_snapshot',
        'dosen_wali_id',
        'diajukan_at',
        'disetujui_oleh',
        'disetujui_pada',
        'ditolak_oleh',
        'ditolak_pada',
        'catatan_admin',
        'is_financial_verified',
        'financial_override_by',
        'financial_override_reason',
        'total_sks_diambil',
        'dispensasi_id',
    ];

    protected $casts = [
        'tgl_krs' => 'datetime',
        'status_krs' => KrsStatusEnum::class,
        'is_paket_snapshot' => 'boolean',
        'diajukan_at' => 'datetime',
        'disetujui_pada' => 'datetime',
        'ditolak_pada' => 'datetime',
        'is_financial_verified' => 'boolean',
        'total_sks_diambil' => 'integer',
    ];

    /**
     * Status KRS yang dianggap "berlaku" (jadwal & presensi mahasiswa
     * hanya sah dihitung dari sini). Sesuaikan dengan konstanta status_krs
     * yang dipakai proses persetujuan KRS pada modul lain.
     */
    public const STATUS_BERLAKU = [
        KrsStatusEnum::DISETUJUI,
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "KRS has been {$eventName}");
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_wali_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function ditolakOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    public function financialOverrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financial_override_by');
    }

    public function dispensasi(): BelongsTo
    {
        return $this->belongsTo(DispensasiAkademik::class, 'dispensasi_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'krs_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(KrsStatusLog::class, 'krs_id')->orderBy('created_at', 'desc');
    }
    public function dosenWaliSnapshot(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_wali_id', 'id');
    }

    public function scopeBerlaku($query)
    {
        return $query->where(
            'status_krs',
            KrsStatusEnum::DISETUJUI->value
        );
    }

    public function scopeCurrentPeriod($query)
    {
        return $query->where('tahun_akademik_id', active_ta_id());
    }



    /*
    |--------------------------------------------------------------------------
    | HasScopeStrategy (App\Domain\Authorization)
    |--------------------------------------------------------------------------
    */

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
        return 'mahasiswa.prodi.fakultas_id';
    }

    public static function getProdiScopeColumn(): ?string
    {
        return 'mahasiswa.prodi_id';
    }

    public static function applyOwnershipScope(Builder $query, User $user, ScopeStrategy $strategy): Builder
    {
        return match ($strategy) {
            // Mahasiswa hanya lihat KRS miliknya sendiri.
            ScopeStrategy::OWNERSHIP_MAHASISWA => $query->whereHas(
                'mahasiswa',
                fn(Builder $q) => $q->where('person_id', $user->person_id),
            ),
            // Dosen Wali hanya lihat KRS yang dosen_wali_id-nya adalah dirinya.
            // Dipakai dosen_wali_id langsung (bukan lewat kelas_dosen_wali) karena
            // kolom ini yang benar-benar menentukan siapa yang berwenang approve/
            // reject KRS tersebut -- lihat KrsPolicy::approve().
            ScopeStrategy::DOSEN_WALI => $query->whereHas(
                'dosenWali',
                fn(Builder $q) => $q->where('person_id', $user->person_id),
            ),
            default => throw new LogicException("Krs tidak mendukung strategy {$strategy->value}"),
        };
    }
}
