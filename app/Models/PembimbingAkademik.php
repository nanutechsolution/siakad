<?php

namespace App\Models;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembimbingAkademik extends Model
{
    use SoftDeletes;

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
