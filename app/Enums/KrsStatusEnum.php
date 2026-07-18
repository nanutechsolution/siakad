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
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Menunggu Persetujuan',
            self::DISETUJUI => 'Disetujui',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::DIAJUKAN => 'warning',
            self::DISETUJUI => 'success',
            self::DITOLAK => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-minus-circle',
            self::DIAJUKAN => 'heroicon-o-clock',
            self::DISETUJUI => 'heroicon-o-check-circle',
            self::DITOLAK => 'heroicon-o-x-circle',
        };
    }

    /** Status yang dihitung sebagai "sudah mengisi KRS". */
    public static function sudahMengisiValues(): array
    {
        return [self::DIAJUKAN->value, self::DISETUJUI->value, self::DITOLAK->value];
    }
}
