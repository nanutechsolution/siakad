<?php

namespace App\Models;

use App\Enums\StatusNilaiKelas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KrsDetail extends Model
{
    use LogsActivity;
    protected $table = 'krs_detail';

    protected $fillable = [
        'krs_id',
        'jadwal_kuliah_id',
        'mata_kuliah_id',
        'kode_mk_snapshot',
        'nama_mk_snapshot',
        'sks_snapshot',
        'activity_type_snapshot',
        'ekuivalensi_id',
        'status_ambil',
        'nilai_angka',
        'nilai_huruf',
        'nilai_indeks',
        'is_published',
        'is_locked',
        'is_edom_filled',
    ];

    protected $casts = [
        'sks_snapshot' => 'integer',
        'nilai_angka' => 'decimal:2',
        'nilai_indeks' => 'decimal:2',
        'is_published' => 'boolean',
        'is_locked' => 'boolean',
        'is_edom_filled' => 'boolean',
    ];

    /**
     * status_ambil yang berarti mata kuliah ini masih aktif diambil
     * (bukan dibatalkan). Sesuaikan huruf kode ini dengan modul KRS Anda
     * jika berbeda -- ini titik yang WAJIB dicek sebelum go-live.
     */
    public const STATUS_AMBIL_AKTIF = ['B']; // contoh: B = Baru/Berjalan

    /**
     * Log ke activity_log setiap kali nilai / status berubah.
     * Sumber "waktu input", "dosen penginput", "waktu publish" bagi BARA
     * — tanpa perlu kolom/tabel baru.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nilai_angka',
                'nilai_huruf',
                'nilai_indeks',
                'is_published',
                'is_locked',
            ])
            ->logOnlyDirty()
            ->useLogName('nilai');
    }
    public function nilaiKomponen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KrsDetailNilai::class, 'krs_detail_id');
    }


    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }
    public function absensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PerkuliahanAbsensi::class, 'krs_detail_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id');
    }
    /** Akses cepat ke mahasiswa lewat krs->mahasiswa */
    public function mahasiswa(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Mahasiswa::class,
            Krs::class,
            'id',            // krs.id
            'id',            // mahasiswas.id
            'krs_id',        // krs_detail.krs_id
            'mahasiswa_id'   // krs.mahasiswa_id
        );
    }

    public function statusNilai(): StatusNilaiKelas
    {
        if ($this->is_locked) {
            return StatusNilaiKelas::TERKUNCI;
        }
        if ($this->is_published) {
            return StatusNilaiKelas::SUDAH_PUBLISH;
        }
        if (filled($this->nilai_huruf)) {
            return StatusNilaiKelas::SUDAH_INPUT;
        }

        return StatusNilaiKelas::BELUM_INPUT;
    }
    public function gradeRevisionLogs(): HasMany
    {
        return $this->hasMany(AkademikGradeRevisionLog::class, 'krs_detail_id')
            ->orderByDesc('created_at');
    }


    /**
     * Relasi ke detail nilai komponen mahasiswa.
     */
    public function detailNilai()
    {
        return $this->hasMany(\App\Models\KrsDetailNilai::class, 'krs_detail_id');
    }
    public function getNilaiKomponen(int $komponenId): float
    {
        return (float) $this->detailNilai->where('komponen_id', $komponenId)->first()?->nilai_angka ?? 0.00;
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status_ambil', self::STATUS_AMBIL_AKTIF);
    }


    /**
     * Semua sesi perkuliahan milik jadwal_kuliah yang sama dengan baris KRS
     * ini -- dipakai sebagai PENYEBUT (denominator) resmi jumlah pertemuan,
     * terlepas dari apakah baris presensi untuk mahasiswa ini sudah dibuat
     * atau belum. Ini sengaja dipisah dari relasi `absensi()` supaya sesi
     * yang belum ada baris presensinya tetap ikut terhitung (dan otomatis
     * dianggap belum hadir), bukan hilang dari perhitungan.
     */
    public function sesiKuliah(): HasManyThrough
    {
        return $this->hasManyThrough(
            PerkuliahanSesi::class,
            JadwalKuliah::class,
            'id',               // FK di tabel jadwal_kuliah yg dituju
            'jadwal_kuliah_id', // FK di tabel perkuliahan_sesi
            'jadwal_kuliah_id', // Local key di krs_detail
            'id'                // Local key di jadwal_kuliah
        );
    }
    public function edomJawabans(): HasMany
    {
        return $this->hasMany(LpmEdomJawaban::class, 'krs_detail_id');
    }

    /**
     * Mengecek apakah KRS Detail ini sudah dievaluasi untuk dosen tertentu.
     */
    public function isEvaluatedFor(string $dosenId): bool
    {
        return $this->edomJawabans()->where('dosen_id', $dosenId)->exists();
    }
}
