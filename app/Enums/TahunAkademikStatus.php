<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TahunAkademikStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case KrsBuka = 'krs_buka';
    case KrsTutup = 'krs_tutup';
    case Perkuliahan = 'perkuliahan';
    case InputNilai = 'input_nilai';
    case NilaiTerkunci = 'nilai_terkunci';
    case NilaiPublish = 'nilai_publish';
    case Selesai = 'selesai';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::KrsBuka => 'KRS Dibuka',
            self::KrsTutup => 'KRS Ditutup',
            self::Perkuliahan => 'Perkuliahan',
            self::InputNilai => 'Input Nilai',
            self::NilaiTerkunci => 'Nilai Terkunci',
            self::NilaiPublish => 'Nilai Dipublish',
            self::Selesai => 'Selesai',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::KrsBuka, self::KrsTutup, self::Perkuliahan => 'info',
            self::InputNilai => 'warning',
            self::NilaiTerkunci => 'danger',
            self::NilaiPublish => 'success',
            self::Selesai => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document',
            self::KrsBuka => 'heroicon-o-lock-open',
            self::KrsTutup => 'heroicon-o-lock-closed',
            self::Perkuliahan => 'heroicon-o-academic-cap',
            self::InputNilai => 'heroicon-o-clipboard-document-list',
            self::NilaiTerkunci => 'heroicon-o-lock-closed',
            self::NilaiPublish => 'heroicon-o-megaphone',
            self::Selesai => 'heroicon-o-check-circle',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => 'Semester baru dibuat, belum ada aktivitas akademik berjalan.',
            self::KrsBuka => 'Mahasiswa sedang dapat mengisi/mengubah KRS.',
            self::KrsTutup => 'KRS dikunci, jadwal kelas final.',
            self::Perkuliahan => 'Periode perkuliahan sedang berjalan.',
            self::InputNilai => 'Dosen sedang menginput nilai.',
            self::NilaiTerkunci => 'Nilai dikunci, menunggu publish.',
            self::NilaiPublish => 'Nilai/KHS sudah dipublish ke mahasiswa.',
            self::Selesai => 'Semester ditutup dan diarsipkan.',
        };
    }

    /**
     * Field tanggal yang WAJIB sudah terisi sebelum semester boleh masuk ke status ini.
     * Dipakai untuk memvalidasi di Model (guard) sekaligus menonaktifkan tombol di UI.
     * key = nama kolom, value = label yang ditampilkan ke user.
     */
    public function requiredFields(): array
    {
        return match ($this) {
            self::KrsBuka => [
                'tgl_mulai_krs' => 'Mulai KRS',
                'tgl_selesai_krs' => 'Selesai KRS',
            ],
            self::Perkuliahan => [
                'tgl_mulai_perkuliahan' => 'Mulai Perkuliahan',
                'tgl_selesai_perkuliahan' => 'Selesai Perkuliahan',
            ],
            self::InputNilai => [
                'tgl_selesai_input_nilai' => 'Batas Akhir Input Nilai',
            ],
            default => [],
        };
    }

    /**
     * Peta transisi valid: status saat ini => [status berikutnya yang diizinkan].
     * Satu-satunya tempat yang mendefinisikan urutan workflow.
     */
    public static function transitions(): array
    {
        return [
            self::Draft->value => [self::KrsBuka],
            self::KrsBuka->value => [self::KrsTutup],
            self::KrsTutup->value => [self::Perkuliahan],
            self::Perkuliahan->value => [self::InputNilai],
            self::InputNilai->value => [self::NilaiTerkunci],
            self::NilaiTerkunci->value => [self::NilaiPublish],
            self::NilaiPublish->value => [self::Selesai],
            self::Selesai->value => [],
        ];
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, static::transitions()[$this->value] ?? [], true);
    }

    public function nextStatus(): ?self
    {
        return static::transitions()[$this->value][0] ?? null;
    }

    public function progressPercent(): int
    {
        $order = array_column(self::cases(), 'value');
        $index = array_search($this->value, $order, true);

        return (int) round((($index + 1) / count($order)) * 100);
    }

    public static function orderedCases(): array
    {
        return self::cases();
    }
}
