<?php

declare(strict_types=1);

namespace App\Filament\Pages\LaporanKeuangan\Contracts;

use Illuminate\Support\Collection;

/**
 * Kontrak yang wajib diimplementasikan setiap Page laporan agar bisa
 * memakai trait HasLaporanFilterAndExport secara seragam.
 */
interface ProvidesLaporanData
{
    /** Skema form filter (array komponen Filament\Forms). */
    public function filterFormSchema(): array;

    /** Peta [key => Label Kolom] sesuai urutan tampil di tabel & export. */
    public function tableHeadings(): array;

    /** Data baris laporan sesuai filter yang sedang aktif. */
    public function tableRows(array $filters): Collection;

    /** Judul laporan, dipakai di header PDF/Excel. */
    public function reportTitle(): string;

    /** Nama dasar file export, tanpa ekstensi (mis. "rekap-tagihan-mahasiswa"). */
    public function exportFileBaseName(): string;
}
