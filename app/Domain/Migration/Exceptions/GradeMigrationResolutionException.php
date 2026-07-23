<?php

declare(strict_types=1);

namespace App\Domain\Migration\Exceptions;

/**
 * Dilempar saat satu baris migrasi nilai gagal diresolusi
 * terhadap data referensi (mahasiswa, prodi, mata kuliah, tahun akademik, skala nilai).
 *
 * Pesan selalu dalam Bahasa Indonesia karena akan langsung ditampilkan
 * ke operator di Filament (Preview & Error Report).
 */
final class GradeMigrationResolutionException extends \RuntimeException
{
    public static function mahasiswaNotFound(string $nim): self
    {
        return new self(
            "Mahasiswa dengan NIM '{$nim}' tidak ditemukan. Pastikan data mahasiswa sudah dimigrasikan terlebih dahulu."
        );
    }

    public static function prodiNotFound(string $kodeProdi): self
    {
        return new self("Program Studi dengan kode '{$kodeProdi}' tidak ditemukan.");
    }

    public static function mataKuliahNotFound(string $kodeMk, string $namaProdi): self
    {
        return new self("Mata Kuliah dengan kode '{$kodeMk}' tidak ditemukan pada Program Studi '{$namaProdi}'.");
    }

    public static function tahunAkademikNotFound(int $tahun, int $semester, string $kodeTahun): self
    {
        return new self(
            "Tahun Akademik untuk tahun {$tahun} semester {$semester} (kode_tahun terhitung: '{$kodeTahun}') tidak ditemukan."
        );
    }

    public static function skalaNilaiNotFound(string $huruf): self
    {
        return new self("Skala nilai huruf '{$huruf}' tidak dikenali di master Skala Nilai.");
    }

    public static function skalaNilaiTidakSesuai(float $angka, string $huruf, float $min, float $max): self
    {
        return new self(sprintf(
            "Nilai angka %.2f tidak sesuai dengan rentang huruf '%s' (%.2f - %.2f). Periksa kembali data sumber.",
            $angka,
            $huruf,
            $min,
            $max,
        ));
    }
}
