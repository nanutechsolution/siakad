<?php

namespace App\Support;

/**
 * Helper untuk memaksa string jadi UTF-8 valid sebelum disimpan ke
 * public property Livewire (array preview, dsb).
 *
 * KENAPA INI PERLU: Livewire men-serialize seluruh public state komponen
 * ke JSON di setiap request (json_encode). Kalau ada satu saja string
 * yang bukan UTF-8 valid — paling sering dari hasil parsing file Excel
 * lama (.xls / Windows-1252) atau data lama di database yang ternyata
 * sudah tersimpan dengan encoding bukan UTF-8 — json_encode() gagal
 * total dan melempar:
 *
 *   InvalidArgumentException: Malformed UTF-8 characters,
 *   possibly incorrectly encoded
 *
 * ...yang membuat SELURUH request update Livewire gagal 500, bukan cuma
 * baris/field yang bermasalah. Karena itu setiap string dari sumber luar
 * (Excel, atau kolom database lama) wajib disaring lewat clean() sebelum
 * masuk ke public property manapun.
 */
class Utf8
{
    public static function clean(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            // Sudah UTF-8, tapi tetap dibersihkan dari byte nyasar yang
            // kadang lolos dari mb_check_encoding pada beberapa edge case.
            return iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: '';
        }

        // Coba deteksi encoding asli; fallback ke Windows-1252 karena itu
        // yang paling umum untuk file Excel lama berbahasa Indonesia/Eropa.
        $detected = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true) ?: 'Windows-1252';

        $converted = @mb_convert_encoding($value, 'UTF-8', $detected);

        if ($converted === false) {
            // Encoding benar-benar tidak dikenali — buang saja byte yang
            // rusak daripada membiarkan seluruh request gagal.
            return iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($value, 'UTF-8', 'UTF-8')) ?: '';
        }

        return $converted;
    }
}
