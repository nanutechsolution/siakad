<?php

namespace App\Enums;

enum StatusNilaiKelas: string
{
    case BELUM_INPUT = 'belum_input';
    case SEBAGIAN_INPUT = 'sebagian_input';
    case SUDAH_INPUT = 'sudah_input';       // semua mhs sudah dinilai dosen, belum publish
    case SUDAH_PUBLISH = 'sudah_publish';   // sudah publish, belum dikunci BARA
    case TERKUNCI = 'terkunci';             // sudah dikunci BARA -> final

    public function label(): string
    {
        return match ($this) {
            self::BELUM_INPUT => 'Belum Input Nilai',
            self::SEBAGIAN_INPUT => 'Input Sebagian',
            self::SUDAH_INPUT => 'Sudah Input (Belum Publish)',
            self::SUDAH_PUBLISH => 'Sudah Publish',
            self::TERKUNCI => 'Terkunci (Final)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BELUM_INPUT => 'danger',
            self::SEBAGIAN_INPUT => 'warning',
            self::SUDAH_INPUT => 'info',
            self::SUDAH_PUBLISH => 'success',
            self::TERKUNCI => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BELUM_INPUT => 'heroicon-o-x-circle',
            self::SEBAGIAN_INPUT => 'heroicon-o-exclamation-triangle',
            self::SUDAH_INPUT => 'heroicon-o-pencil-square',
            self::SUDAH_PUBLISH => 'heroicon-o-check-circle',
            self::TERKUNCI => 'heroicon-o-lock-closed',
        };
    }

    public static function fromCounts(int $totalMhs, int $sudahInput, int $sudahPublish, int $terkunci): self
    {
        if ($totalMhs === 0) {
            return self::BELUM_INPUT;
        }

        if ($terkunci === $totalMhs) {
            return self::TERKUNCI;
        }

        if ($sudahPublish === $totalMhs) {
            return self::SUDAH_PUBLISH;
        }

        if ($sudahInput === $totalMhs) {
            return self::SUDAH_INPUT;
        }

        if ($sudahInput > 0) {
            return self::SEBAGIAN_INPUT;
        }

        return self::BELUM_INPUT;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $s) => [$s->value => $s->label()])
            ->toArray();
    }
}
