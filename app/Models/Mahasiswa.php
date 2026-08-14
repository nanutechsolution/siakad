<?php

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Enums\StatusRisikoAkademikEnum;
use App\Models\Concerns\HasStudentProfileRelations;
use App\Models\Concerns\VisibleToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property-read \App\Models\MahasiswaKelas|null $kelasAktif
 * @property-read \App\Models\RefPerson|null $dosenWali
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\Krs> $krs
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\TagihanMahasiswa> $tagihan
 */

class Mahasiswa extends Model implements HasScopeStrategy
{
    use HasFactory, HasUuids, SoftDeletes, HasStudentProfileRelations;
    use VisibleToUser;
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

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
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
        return 'prodi.fakultas_id';
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
     * Get the bills associated with the student.
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id');
    }


    public function tagihanMahasiswas(): HasMany
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Total tunggakan yang belum lunas (status_bayar != LUNAS).
     * sisa_tagihan sudah generated column di DB (total_tagihan - total_bayar).
     */
    public function totalTunggakan(): float
    {
        return (float) $this->tagihanMahasiswas()
            ->where('status_bayar', '!=', 'LUNAS')
            ->sum('sisa_tagihan');
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
    /**
     * Relasi ke tabel histori mahasiswa_kelas.
     * Seorang mahasiswa bisa memiliki banyak riwayat kelas sepanjang masa studinya.
     */
    public function mahasiswaKelas(): HasMany
    {
        return $this->hasMany(MahasiswaKelas::class, 'mahasiswa_id', 'id');
    }
    public function scopeBelumBerkelas(Builder $query): Builder
    {
        return $query->whereDoesntHave(
            'mahasiswaKelas',
            fn(Builder $q) => $q->whereNull('tanggal_keluar')
        );
    }

    /**
     * Cari akun User (login) milik mahasiswa ini via person_id.
     * Bukan relasi Eloquent karena users<->mahasiswas hanya sibling lewat ref_person,
     * bukan parent-child, jadi query langsung lebih jelas daripada hasOneThrough yang dipaksakan.
     */
    public function akunUser(): ?\App\Models\User
    {
        return \App\Models\User::where('person_id', $this->person_id)->first();
    }

    public function isKrsPaket(): bool
    {
        return ($this->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET';
    }
    public function edomProgress()
    {
        return $this->hasMany(EdomProgress::class, 'mahasiswa_id');
    }

    public function tagihanNonRegulers()
    {
        return $this->hasMany(
            TagihanNonReguler::class,
            'mahasiswa_id'
        );
    }

    /** Nilai final per mata kuliah (sumber resmi untuk Transkrip Sementara). */
    public function transkrip(): HasMany
    {
        return $this->hasMany(AkademikTranskrip::class, 'mahasiswa_id');
    }

    public function getNamaLengkapAttribute(): ?string
    {
        return $this->person?->nama_lengkap;
    }


    public function pembimbingAkademik()
    {
        return $this->hasMany(PembimbingAkademik::class, 'mahasiswa_id');
    }

    public function mulaiStudiTahunAkademik()
    {
        return $this->belongsTo(
            RefTahunAkademik::class,
            'mulai_studi_tahun_akademik_id'
        );
    }

    public function semesterPada(RefTahunAkademik $tahunAkademik): ?int
    {
        $mulaiStudi = $this->mulaiStudiTahunAkademik;

        if (! $mulaiStudi) {
            throw new \LogicException(
                "Mahasiswa {$this->nim} belum memiliki tahun akademik mulai studi."
            );
        }

        $startYear = (int) substr($mulaiStudi->kode_tahun, 0, 4);
        $startSemester = (int) $mulaiStudi->semester;

        $currentYear = (int) substr($tahunAkademik->kode_tahun, 0, 4);
        $currentSemester = (int) $tahunAkademik->semester;

        $startIndex = ($startYear * 2) + ($startSemester - 1);
        $currentIndex = ($currentYear * 2) + ($currentSemester - 1);

        // Belum mulai studi pada periode ini.
        if ($currentIndex < $startIndex) {
            return null;
        }

        return $currentIndex - $startIndex + 1;
    }

    public function sudahMulaiStudiPada(RefTahunAkademik $tahunAkademik): bool
    {
        $mulaiStudi = $this->mulaiStudiTahunAkademik;

        if (! $mulaiStudi) {
            return false;
        }

        return (int) $tahunAkademik->kode_tahun >=
            (int) $mulaiStudi->kode_tahun;
    }

    public function statusRisiko(): StatusRisikoAkademikEnum
    {
        $riwayat = $this->riwayatStatus;

        $terakhir = $riwayat->first();

        if (! $terakhir) {
            return StatusRisikoAkademikEnum::BELUM_ADA_DATA;
        }

        if ((float) $terakhir->ipk < 2.00) {
            return StatusRisikoAkademikEnum::KRITIS;
        }

        $duaTerakhir = $riwayat->take(2)->values();

        if (
            $duaTerakhir->count() === 2 &&
            (float) $duaTerakhir[0]->ips < (float) $duaTerakhir[1]->ips
        ) {
            return StatusRisikoAkademikEnum::WASPADA;
        }

        return StatusRisikoAkademikEnum::AMAN;
    }
}
