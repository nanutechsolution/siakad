<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception domain khusus untuk aturan bisnis modul Manajemen Kelas.
 * Pesannya aman ditampilkan langsung ke user lewat Notification.
 */
class ManajemenKelasException extends Exception
{
    public static function kapasitasPenuh(int $kapasitas): self
    {
        return new self("Kelas ini sudah penuh (kapasitas {$kapasitas} mahasiswa). Tambah kapasitas dulu di menu Kelas atau pilih kelas lain.");
    }

    public static function sudahDiKelasYangSama(): self
    {
        return new self('Mahasiswa ini sudah menjadi anggota aktif kelas tersebut.');
    }

    public static function belumPunyaKelasAktif(): self
    {
        return new self('Mahasiswa ini belum punya keanggotaan kelas aktif untuk dipindahkan. Gunakan "Tempatkan ke Kelas" alih-alih "Pindahkan Kelas".');
    }

    public static function kelasTujuanSamaDenganAsal(): self
    {
        return new self('Kelas tujuan tidak boleh sama dengan kelas asal.');
    }
}
