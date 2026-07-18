<?php

declare(strict_types=1);

namespace App\Filament\Widgets\MonitoringKrs\Concerns;

use App\Domain\Authorization\Services\OrganizationResolver;
use App\Models\Krs;
use App\Models\KrsStatusLog;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Satu-satunya tempat query dasar Mahasiswa/Krs/RefProdi/KrsStatusLog
 * dibangun untuk SELURUH widget & halaman Monitoring KRS.
 *
 * SEBELUMNYA setiap widget membangun query sendiri-sendiri
 * (Mahasiswa::query()->when($this->pageFilters['prodi_id'] ?? null, ...))
 * tanpa baseline scope organisasi. "when()" tidak menambahkan apa pun kalau
 * filter belum dipilih user -> saat pageFilters kosong (kondisi default
 * begitu halaman dibuka), Admin Prodi/Kaprodi/Admin Fakultas melihat data
 * se-universitas.
 *
 * Trait ini mewajibkan Model::visibleTo($user) sebagai lapis PALING DASAR
 * (tidak bisa dilewati, tidak bergantung pageFilters), baru di atasnya
 * pageFilters diterapkan sebagai PENYEMPIT tambahan. Kalau pageFilters
 * berisi prodi_id/fakultas_id di luar hak akses user (mis. hasil tampering
 * request), hasilnya otomatis 0 baris karena sudah kena whereIn
 * accessibleProdiIds() duluan di dalam visibleTo() -- bukan membocorkan
 * data organisasi lain.
 */
trait ScopedMonitoringQueries
{
    protected function scopedMahasiswaQuery(): Builder
    {
        return Mahasiswa::query()
            ->visibleTo(auth()->user())
            ->when($this->pageFilters['prodi_id'] ?? null, fn(Builder $q, $v) => $q->where('prodi_id', $v))
            ->when($this->pageFilters['fakultas_id'] ?? null, fn(Builder $q, $v) => $q
                ->whereHas('prodi', fn(Builder $qq) => $qq->where('fakultas_id', $v)));
    }

    protected function scopedKrsQuery(): Builder
    {
        return Krs::query()
            ->visibleTo(auth()->user())
            ->when($this->pageFilters['prodi_id'] ?? null, fn(Builder $q, $v) => $q
                ->whereHas('mahasiswa', fn(Builder $qq) => $qq->where('prodi_id', $v)))
            ->when($this->pageFilters['fakultas_id'] ?? null, fn(Builder $q, $v) => $q
                ->whereHas('mahasiswa.prodi', fn(Builder $qq) => $qq->where('fakultas_id', $v)));
    }

    /**
     * KrsStatusLog tidak implement HasScopeStrategy sendiri (ini log, bukan
     * data master) -> scope diterapkan lewat relasi ke Krs yang SUDAH
     * discope, bukan diterapkan ulang secara manual.
     */
    protected function scopedKrsStatusLogQuery(): Builder
    {
        return KrsStatusLog::query()->whereHas('krs', function (Builder $q) {
            $q->visibleTo(auth()->user())
                ->when($this->pageFilters['prodi_id'] ?? null, fn(Builder $qq, $v) => $qq
                    ->whereHas('mahasiswa', fn(Builder $qqq) => $qqq->where('prodi_id', $v)))
                ->when($this->pageFilters['fakultas_id'] ?? null, fn(Builder $qq, $v) => $qq
                    ->whereHas('mahasiswa.prodi', fn(Builder $qqq) => $qqq->where('fakultas_id', $v)));
        });
    }

    /**
     * Daftar prodi yang boleh muncul sebagai label/kategori di chart --
     * dibatasi ke accessibleProdiIds() user, BUKAN semua RefProdi aktif.
     *
     * @return Collection<int, RefProdi>
     */
    protected function scopedProdiForChart(): Collection
    {
        $accessibleIds = app(OrganizationResolver::class)->accessibleProdiIds(auth()->user());

        if ($accessibleIds === []) {
            return collect();
        }

        return RefProdi::query()
            ->whereIn('id', $accessibleIds)
            ->where('is_active', true)
            ->when($this->pageFilters['prodi_id'] ?? null, fn(Builder $q, $v) => $q->where('id', $v))
            ->when($this->pageFilters['fakultas_id'] ?? null, fn(Builder $q, $v) => $q->where('fakultas_id', $v))
            ->get(['id', 'nama_prodi']);
    }
}
