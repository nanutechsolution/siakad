<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmDokumen extends Model
{
    protected $fillable = [
        'kode_dokumen',
        'nama_dokumen',
        'jenis',
        'prodi_id',
        'unit_kerja_id',
        'standar_id',
        'file_path',
        'deskripsi',
        'versi',
        'status',
        'is_active',
        'tgl_berlaku',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tgl_berlaku' => 'date',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(LpmUnitKerja::class, 'unit_kerja_id');
    }

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LpmDokumenApproval::class, 'dokumen_id');
    }

    public function riwayats(): HasMany
    {
        return $this->hasMany(LpmDokumenRiwayat::class, 'dokumen_id')->orderByDesc('tanggal');
    }

    /**
     * Status disahkan penuh: ketiga peran (Penyusun, Pemeriksa, Pengesah)
     * sudah APPROVED untuk versi berjalan saat ini.
     */
    public function isFullyApproved(): bool
    {
        $peranWajib = ['PENYUSUN', 'PEMERIKSA', 'PENGESAH'];

        $approved = $this->approvals()
            ->whereIn('peran', $peranWajib)
            ->where('status', 'APPROVED')
            ->pluck('peran')
            ->unique();

        return $approved->count() === count($peranWajib);
    }
}
