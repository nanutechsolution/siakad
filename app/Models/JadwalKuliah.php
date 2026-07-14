<?php

namespace App\Models;

use App\Enums\StatusNilaiKelas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JadwalKuliah extends Model
{
    use SoftDeletes, HasUuids;

    /**
     * Nama tabel di database.
     */
    protected $table = 'jadwal_kuliah';

    /**
     * Tipe Primary Key karena menggunakan UUID (char 36).
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom-kolom yang dapat diisi.
     */
    protected $fillable = [
        'tahun_akademik_id',
        'kurikulum_id',
        'mata_kuliah_id',
        'kelas_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruang_id',
        'kuota_kelas',
        'isi_kelas',
    ];

    /**
     * Casting tipe data kolom.
     */
    protected $casts = [
        'kuota_kelas' => 'integer',
        'isi_kelas' => 'integer',
        // Jam biarkan string agar kompatibel dengan format 'H:i' di Filament TimePicker
    ];
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    public function krsDetail(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KrsDetail::class, 'jadwal_kuliah_id');
    }
    /**
     * Relasi ke Tahun Akademik.
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Relasi ke Kurikulum (Opsional).
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(MasterKurikulum::class, 'kurikulum_id');
    }

     /**
     * Scope inti untuk tabel Monitoring BARA: menambahkan kolom agregat
     *   jumlah_mahasiswa, jumlah_sudah_input, jumlah_sudah_publish, jumlah_terkunci
     * berbasis subquery ke krs_detail — cukup 1 query, tanpa N+1.
     */
    public function scopeWithNilaiStats(Builder $query): Builder
    {
        return $query
            ->withCount([
                'krsDetail as jumlah_mahasiswa',
                'krsDetail as jumlah_sudah_input' => fn (Builder $q) => $q->whereNotNull('nilai_huruf'),
                'krsDetail as jumlah_sudah_publish' => fn (Builder $q) => $q->where('is_published', true),
                'krsDetail as jumlah_terkunci' => fn (Builder $q) => $q->where('is_locked', true),
            ]);
    }

    /** Dipanggil setelah withNilaiStats(); butuh kolom-kolom di atas sudah ter-load */
    public function getStatusNilaiAttribute(): StatusNilaiKelas
    {
        return StatusNilaiKelas::fromCounts(
            totalMhs: (int) ($this->jumlah_mahasiswa ?? $this->krsDetail()->count()),
            sudahInput: (int) ($this->jumlah_sudah_input ?? $this->krsDetail()->whereNotNull('nilai_huruf')->count()),
            sudahPublish: (int) ($this->jumlah_sudah_publish ?? $this->krsDetail()->where('is_published', true)->count()),
            terkunci: (int) ($this->jumlah_terkunci ?? $this->krsDetail()->where('is_locked', true)->count()),
        );
    }

    /** Filter helper dipakai oleh Filament table filter "Status Nilai" */
    public function scopeStatusNilai(Builder $query, StatusNilaiKelas $status): Builder
    {
        return match ($status) {
            StatusNilaiKelas::BELUM_INPUT => $query->whereDoesntHave('krsDetail', fn ($q) => $q->whereNotNull('nilai_huruf')),
            StatusNilaiKelas::SEBAGIAN_INPUT => $query
                ->whereHas('krsDetail', fn ($q) => $q->whereNotNull('nilai_huruf'))
                ->whereHas('krsDetail', fn ($q) => $q->whereNull('nilai_huruf')),
            StatusNilaiKelas::SUDAH_INPUT => $query
                ->whereDoesntHave('krsDetail', fn ($q) => $q->whereNull('nilai_huruf'))
                ->whereDoesntHave('krsDetail', fn ($q) => $q->where('is_published', false))
                ->has('krsDetail'),
            StatusNilaiKelas::SUDAH_PUBLISH => $query
                ->whereHas('krsDetail', fn ($q) => $q->where('is_published', true))
                ->whereDoesntHave('krsDetail', fn ($q) => $q->where('is_locked', false)->where('is_published', false)),
            StatusNilaiKelas::TERKUNCI => $query
                ->has('krsDetail')
                ->whereDoesntHave('krsDetail', fn ($q) => $q->where('is_locked', false)),
        };
    }
    /**
     * Relasi ke Mata Kuliah.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Relasi ke Kelas (Asumsi Anda punya model Kelas / RefKelas).
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Ruang (Asumsi Anda punya model RefRuang).
     */
    public function ruang(): BelongsTo
    {
        return $this->belongsTo(RefRuang::class, 'ruang_id');
    }
    // Relasi ke tabel pivot jadwal_kuliah_dosen
    public function dosenPengajar(): HasMany
    {
        return $this->hasMany(JadwalKuliahDosen::class, 'jadwal_kuliah_id', 'id');
    }
    /**
     * Relasi ke Dosen Pengajar (Pivot / Child).
     */
    public function dosenPengajars(): HasMany
    {
        return $this->hasMany(JadwalKuliahDosen::class, 'jadwal_kuliah_id');
    }

    // Relasi ke krs_detail (peserta kelas)
    public function krsDetails(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'jadwal_kuliah_id', 'id');
    }
    /** 
     * Relasi ke data Komponen Nilai (Bobot Penilaian)
     */
    public function komponenNilai()
    {
        return $this->hasMany(JadwalKomponenNilai::class);
    }

    public function sesiPerkuliahan(): HasMany
    {
        return $this->hasMany(PerkuliahanSesi::class, 'jadwal_kuliah_id');
    }
    /** Dosen pengampu (bisa lebih dari satu, is_koordinator/is_penilai) */
    public function dosenPengampu(): BelongsToMany
    {
        return $this->belongsToMany(TrxDosen::class, 'jadwal_kuliah_dosen', 'jadwal_kuliah_id', 'dosen_id')
            ->withPivot(['is_koordinator', 'is_penilai', 'rencana_tatap_muka']);
    }
    public function dosenPengampus(): HasMany
    {
        return $this->hasMany(JadwalKuliahDosen::class, 'jadwal_kuliah_id', 'id');
    }
    /**
     * Scope: hanya jadwal yang diampu oleh dosen tertentu.
     */
    public function scopeUntukDosen($query, string $dosenId)
    {
        return $query->whereHas('dosenPengampu', fn($q) => $q->where('dosen_id', $dosenId));
    }

    public function dosen(): BelongsToMany
    {
        return $this->belongsToMany(TrxDosen::class, 'jadwal_kuliah_dosen', 'jadwal_kuliah_id', 'dosen_id')
            ->withPivot('is_koordinator', 'is_penilai', 'rencana_tatap_muka')
            ->withTimestamps();
    }

    /**
     * Mengecek apakah dosen ini terdaftar sebagai penilai.
     * Menerima parameter berupa $dosenId (string) dari Policy.
     */
    public function isPenilaiOleh(string $dosenId): bool
    {
        return \Illuminate\Support\Facades\DB::table('jadwal_kuliah_dosen')
            ->where('jadwal_kuliah_id', (string) $this->id)
            ->where('dosen_id', $dosenId)
            ->where('is_penilai', 1)
            ->exists();
    }

    /**
     * Mengecek apakah dosen ini terdaftar sebagai koordinator kelas.
     * Menerima parameter berupa $dosenId (string) dari Policy.
     */
    public function isKoordinatorOleh(string $dosenId): bool
    {
        return \Illuminate\Support\Facades\DB::table('jadwal_kuliah_dosen')
            ->where('jadwal_kuliah_id', (string) $this->id)
            ->where('dosen_id', $dosenId)
            ->where('is_koordinator', 1) // Sesuaikan nama kolom ini jika berbeda di database Anda
            ->exists();
    }
}
