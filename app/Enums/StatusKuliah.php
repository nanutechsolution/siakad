<?php

namespace App\Enums;

enum StatusKuliah: string
{
    case AKTIF = 'A';
    case CUTI = 'C';
    case NON_AKTIF = 'N'; // Mangkir / Alfa
    case LULUS = 'L';
    case DROP_OUT = 'D'; // Putus Studi
    case KELUAR = 'K'; // Mengundurkan Diri
    case DOUBLE_DEGREE = 'G';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::CUTI => 'Cuti',
            self::NON_AKTIF => 'Non-Aktif',
            self::LULUS => 'Lulus',
            self::DROP_OUT => 'Drop Out',
            self::KELUAR => 'Keluar',
            self::DOUBLE_DEGREE => 'Double Degree',
        };
    }
}
