<?php

declare(strict_types=1);

namespace App\Filament\Pages\LaporanKeuangan\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Kontrak setiap Page laporan. PERUBAHAN dari versi sebelumnya:
 * `tableRows(): Collection` (fetch semua data) DIHAPUS, diganti
 * `query(): Builder` (query BELUM dieksekusi).
 *
 * Satu Builder yang sama dipakai untuk DUA jalur berbeda:
 * - TABLE  : Filament memanggil ->paginate() sendiri (LIMIT/OFFSET native).
 * - EXPORT : dibaca bertahap via ->chunk() / Maatwebsite WithChunkReading,
 *            TIDAK PERNAH di-->get() sekaligus.
 */
interface ProvidesLaporanData
{
    /** Skema form filter (array komponen Filament\Forms). */
    public function filterFormSchema(): array;

    /** Peta [key => Label Kolom] sesuai urutan tampil di tabel & export. */
    public function tableHeadings(): array;

    /**
     * Query BELUM dieksekusi (tidak boleh memanggil ->get()/->all() di
     * dalam method ini). Filament yang akan memanggil ->paginate();
     * proses export yang akan memanggil ->chunk().
     */
    public function query(array $filters): Builder;

    /** Judul laporan, dipakai di header PDF/Excel. */
    public function reportTitle(): string;

    /** Nama dasar file export, tanpa ekstensi (mis. "rekap-tagihan-mahasiswa"). */
    public function exportFileBaseName(): string;
}
