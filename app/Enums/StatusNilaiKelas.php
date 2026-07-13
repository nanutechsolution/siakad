<?php

namespace App\Enums;

enum StatusNilaiKelas: string
{
    case BELUM_INPUT = 'belum_input';
    case SUDAH_INPUT = 'sudah_input'; // Sebagian/semua diinput tapi belum publish
    case SUDAH_PUBLISH = 'sudah_publish';
    case TERKUNCI = 'terkunci';

    public function label(): string
    {
        return match($this) {
            self::BELUM_INPUT => 'Belum Input',
            self::SUDAH_INPUT => 'Draft (Sudah Input)',
            self::SUDAH_PUBLISH => 'Sudah Publish',
            self::TERKUNCI => 'Final (Terkunci)',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BELUM_INPUT => 'danger',
            self::SUDAH_INPUT => 'warning',
            self::SUDAH_PUBLISH => 'info',
            self::TERKUNCI => 'success',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BELUM_INPUT => 'heroicon-o-x-circle',
            self::SUDAH_INPUT => 'heroicon-o-document-text',
            self::SUDAH_PUBLISH => 'heroicon-o-check-circle',
            self::TERKUNCI => 'heroicon-o-lock-closed',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])->toArray();
    }
}