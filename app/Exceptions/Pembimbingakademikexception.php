<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception domain khusus untuk aturan bisnis modul Pembimbing Akademik.
 *
 * Pesannya didesain agar aman ditampilkan langsung ke user lewat
 * Filament\Notifications\Notification (bukan pesan teknis/stack trace).
 */
class PembimbingAkademikException extends Exception
{
    public static function targetKosong(): self
    {
        return new self('Target penugasan (kelas atau mahasiswa) belum dipilih.');
    }

    public static function konfigurasiBelumDiatur(): self
    {
        return new self('Konfigurasi mode pembimbing untuk Program Studi dan Angkatan ini belum diatur/aktif. Silakan atur terlebih dahulu di menu Konfigurasi Pembimbing.');
    }

    public static function sudahAdaPembimbingAktif(): self
    {
        return new self('Target ini sudah memiliki pembimbing aktif dengan jenis yang sama. Gunakan menu Mutasi Pembimbing bila ingin menggantinya.');
    }

    public static function dosenPenggantiSama(): self
    {
        return new self('Dosen pengganti tidak boleh sama dengan dosen yang sedang aktif saat ini.');
    }

    public static function sudahDicoverWaliKelas(): self
    {
        return new self('Mahasiswa ini sudah tercakup oleh Dosen Wali tingkat kelas yang aktif (kemungkinan besar konfigurasi mode berubah dari Per Kelas ke Per Mahasiswa). Batalkan dulu wali tingkat kelasnya di menu Mutasi bila memang ingin memberi wali individual terpisah.');
    }

    public static function tanggalMulaiTidakValid(): self
    {
        return new self('Tanggal mulai penugasan baru tidak boleh lebih awal dari tanggal mulai penugasan sebelumnya.');
    }
}
