<?php

namespace App\Enums;

enum KrsStatusEnum: string
{
    case DRAFT = 'DRAFT';
    case DIAJUKAN = 'DIAJUKAN';
    case DISETUJUI = 'DISETUJUI';
    case DITOLAK = 'DITOLAK';
    case DIBATALKAN = 'DIBATALKAN';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Menunggu Persetujuan',
            self::DISETUJUI => 'Disetujui',
            self::DITOLAK => 'Ditolak',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::DIAJUKAN => 'warning',
            self::DISETUJUI => 'success',
            self::DITOLAK => 'danger',
            self::DIBATALKAN => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-minus-circle',
            self::DIAJUKAN => 'heroicon-o-clock',
            self::DISETUJUI => 'heroicon-o-check-circle',
            self::DITOLAK => 'heroicon-o-x-circle',
            self::DIBATALKAN => 'heroicon-o-no-symbol',
        };
    }

    /** Status final: tidak boleh diubah lagi kecuali lewat "Buka Kembali". */
    public function isFinal(): bool
    {
        return in_array($this, [self::DISETUJUI, self::DIBATALKAN], true);
    }


    /** Status yang dihitung sebagai "sudah mengisi KRS". */
    public static function sudahMengisiValues(): array
    {
        return [self::DIAJUKAN->value, self::DISETUJUI->value, self::DITOLAK->value];
    }

    /** Opsi SelectFilter status_krs, konsisten dengan label & warna. */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}
