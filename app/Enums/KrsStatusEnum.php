<?php

namespace App\Enums;

enum KrsStatusEnum: string
{
    case DRAFT = 'DRAFT';
    case DIAJUKAN = 'DIAJUKAN';
    case DISETUJUI = 'DISETUJUI';
    case DITOLAK = 'DITOLAK';
    
    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Menunggu Persetujuan',
            self::DISETUJUI => 'Disetujui',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::DIAJUKAN => 'warning',
            self::DISETUJUI => 'success',
            self::DITOLAK => 'danger',
        };
    }
}