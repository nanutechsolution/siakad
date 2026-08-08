<?php

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PembimbingAkademik extends Model implements HasScopeStrategy
{
    use SoftDeletes, LogsActivity;

    protected $table = 'pembimbing_akademik';

    protected $fillable = [
        'kelas_id',
        'mahasiswa_id',
        'dosen_id',
        'jenis',
        'is_primary',
        'semester_mulai_id',
        'semester_selesai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'nomor_sk',
        'tanggal_sk',
        'alasan',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'jenis' => PembimbingAkademikJenis::class,
        'status' => PembimbingAkademikStatus::class,
        'is_primary' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sk' => 'date',
    ];
    /*
    |--------------------------------------------------------------------------
    | Authorization / Scope Strategy
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
        return null;
    }

    public static function getProdiScopeColumn(): ?string
    {
        return null;
    }

    /**
     * Apply scope berdasarkan ownership.
     */
    public static function applyOwnershipScope(
        Builder $query,
        User $user,
        ScopeStrategy $strategy
    ): Builder {
        return match ($strategy) {
            /*
             * Mahasiswa hanya boleh melihat penugasan
             * yang ditujukan langsung kepada dirinya.
             */
            ScopeStrategy::OWNERSHIP_MAHASISWA =>
            $query->whereHas(
                'mahasiswa',
                fn(Builder $mahasiswa) =>
                $mahasiswa->where('person_id', $user->person_id)
            ),

            /*
             * Dosen hanya boleh melihat mahasiswa/kelas
             * yang memang menjadi tanggung jawabnya
             * melalui pembimbing_akademik.
             *
             * Tidak lagi menggunakan kelas.dosenWali.
             */
            ScopeStrategy::DOSEN_WALI =>
            $query
                ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                ->where('dosen_id', $user->dosen?->id),

            default => throw new \LogicException(
                "PembimbingAkademik tidak mendukung strategy {$strategy->value}"
            ),
        };
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pembimbing-akademik')
            ->logOnly([
                'kelas_id',
                'mahasiswa_id',
                'dosen_id',
                'jenis',
                'is_primary',
                'semester_mulai_id',
                'semester_selesai_id',
                'tanggal_mulai',
                'tanggal_selesai',
                'nomor_sk',
                'tanggal_sk',
                'alasan',
                'keterangan',
                'status',
            ])
            ->logOnlyDirty();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'Menambahkan pembimbing akademik',
            'updated' => 'Mengubah data pembimbing akademik',
            'deleted' => 'Menghapus pembimbing akademik',
            'restored' => 'Memulihkan pembimbing akademik',
            default => $eventName,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }

    public function semesterMulai(): BelongsTo
    {
        return $this->belongsTo(
            RefTahunAkademik::class,
            'semester_mulai_id'
        );
    }

    public function semesterSelesai(): BelongsTo
    {
        return $this->belongsTo(
            RefTahunAkademik::class,
            'semester_selesai_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAktif($query)
    {
        return $query->where('status', PembimbingAkademikStatus::AKTIF);
    }

    public function scopeDosenWali($query)
    {
        return $query->where('jenis', PembimbingAkademikJenis::DOSEN_WALI);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAktif(): bool
    {
        return $this->status === PembimbingAkademikStatus::AKTIF;
    }

    public function isPerKelas(): bool
    {
        return ! is_null($this->kelas_id);
    }

    public function isPerMahasiswa(): bool
    {
        return ! is_null($this->mahasiswa_id);
    }

    public function scopeDosenWaliAktif($query)
    {
        return $query
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->where('is_primary', true);
    }
}
