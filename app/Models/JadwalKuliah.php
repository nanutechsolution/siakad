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
     * Scope untuk filter status nilai kelas
     */
    public function scopeStatusNilai(Builder $query, StatusNilaiKelas $status): Builder
    {
        return match ($status) {
            // TERKUNCI: Kelas punya data KRS, dan TIDAK ADA mahasiswa yang status locked-nya false (Semua locked)
            StatusNilaiKelas::TERKUNCI => $query->has('krsDetails')
                ->whereDoesntHave('krsDetails', fn($q) => $q->where('is_locked', false)),

            // SUDAH_PUBLISH: Punya data KRS, TIDAK ADA yang belum publish, TAPI masih ada yang belum di-lock
            StatusNilaiKelas::SUDAH_PUBLISH => $query->has('krsDetails')
                ->whereDoesntHave('krsDetails', fn($q) => $q->where('is_published', false))
                ->whereHas('krsDetails', fn($q) => $q->where('is_locked', false)),

            // SUDAH_INPUT (DRAFT): Ada minimal 1 mahasiswa yang nilainya sudah diisi, TAPI masih ada yang belum di-publish
            StatusNilaiKelas::SUDAH_INPUT => $query->whereHas('krsDetails', fn($q) => $q->whereNotNull('nilai_angka'))
                ->whereHas('krsDetails', fn($q) => $q->where('is_published', false)),

            // BELUM_INPUT: Kelas belum punya data KRS, ATAU semua data KRS nilai angkanya masih kosong/null
            StatusNilaiKelas::BELUM_INPUT => $query->whereDoesntHave('krsDetails', fn($q) => $q->whereNotNull('nilai_angka')),
        };
    }
    public function scopeWithNilaiStats(Builder $query): void
    {
        $query->withCount([
            'krsDetails as jumlah_mahasiswa', // Pastikan relasinya bernama krsDetails
            'krsDetails as total_published' => fn($q) => $q->where('is_published', true),
            'krsDetails as total_locked' => fn($q) => $q->where('is_locked', true),
            'krsDetails as total_has_nilai' => fn($q) => $q->whereNotNull('nilai_angka'),
        ]);
    }

    // Accessor dinamis untuk mendapatkan status kelas berdasarkan kalkulasi agregat krsDetails
    public function getStatusNilaiAttribute(): StatusNilaiKelas
    {
        $totalMhs = $this->jumlah_mahasiswa ?? 0;

        if ($totalMhs === 0) return StatusNilaiKelas::BELUM_INPUT;
        if ($this->total_locked > 0 && $this->total_locked === $totalMhs) return StatusNilaiKelas::TERKUNCI;
        if ($this->total_published > 0 && $this->total_published === $totalMhs) return StatusNilaiKelas::SUDAH_PUBLISH;
        if ($this->total_has_nilai > 0) return StatusNilaiKelas::SUDAH_INPUT;

        return StatusNilaiKelas::BELUM_INPUT;
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
    public function dosenPengampu(): HasMany
    {
        return $this->hasMany(JadwalKuliahDosen::class, 'jadwal_kuliah_id');
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
