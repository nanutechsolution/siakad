<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relasi ke Dosen Pengajar (Pivot / Child).
     */
    public function dosenPengajars(): HasMany
    {
        return $this->hasMany(JadwalKuliahDosen::class, 'jadwal_kuliah_id');
    }

    /**
     * Relasi ke data Komponen Nilai (Bobot Penilaian)
     */
    public function komponenNilai(): HasMany
    {
        // Parameter ke-2 adalah Foreign Key di tabel komponen_nilais yang mengarah ke jadwal_kuliah.
        // Sesuaikan 'jadwal_kuliah_id' dengan nama kolom asli di database Anda (misal: 'jadwal_id').
        return $this->hasMany(RefKomponenNilai::class, 'jadwal_kuliah_id');
    }
}
