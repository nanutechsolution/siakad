<?php

namespace App\Support;

class Terbilang
{
    /**
     * Konversi angka menjadi teks terbilang Bahasa Indonesia, lengkap dengan akhiran "Rupiah".
     * Mendukung nilai hingga triliunan. Satu-satunya sumber logika terbilang di aplikasi ini —
     * jangan duplikasi fungsi ini di file lain.
     */
    public static function make(int $angka): string
    {
        return trim(self::convert(abs($angka))) . ' Rupiah';
    }

    protected static function convert(int $angka): string
    {
        $baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

        if ($angka < 12) {
            return ' ' . $baca[$angka];
        }

        if ($angka < 20) {
            return self::convert($angka - 10) . ' Belas';
        }

        if ($angka < 100) {
            return self::convert(intdiv($angka, 10)) . ' Puluh' . self::convert($angka % 10);
        }

        if ($angka < 200) {
            return ' Seratus' . self::convert($angka - 100);
        }

        if ($angka < 1000) {
            return self::convert(intdiv($angka, 100)) . ' Ratus' . self::convert($angka % 100);
        }

        if ($angka < 2000) {
            return ' Seribu' . self::convert($angka - 1000);
        }

        if ($angka < 1000000) {
            return self::convert(intdiv($angka, 1000)) . ' Ribu' . self::convert($angka % 1000);
        }

        if ($angka < 1000000000) {
            return self::convert(intdiv($angka, 1000000)) . ' Juta' . self::convert($angka % 1000000);
        }

        if ($angka < 1000000000000) {
            return self::convert(intdiv($angka, 1000000000)) . ' Miliar' . self::convert($angka % 1000000000);
        }

        return self::convert(intdiv($angka, 1000000000000)) . ' Triliun' . self::convert($angka % 1000000000000);
    }
}
