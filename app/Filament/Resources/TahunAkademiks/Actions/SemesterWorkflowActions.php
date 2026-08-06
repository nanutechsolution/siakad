<?php

namespace App\Filament\Resources\TahunAkademiks\Actions;

use App\Enums\TahunAkademikStatus;
use App\Models\RefTahunAkademik;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Throwable;

class SemesterWorkflowActions
{
    public static function bukaKrs(): Action
    {
        return static::base('buka_krs', 'Buka KRS', 'heroicon-o-lock-open', 'info', TahunAkademikStatus::KrsBuka)
            ->modalDescription('Mahasiswa akan dapat mengisi dan mengubah KRS setelah periode ini dibuka.')
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'bukaKrs', 'KRS berhasil dibuka'));
    }

    public static function tutupKrs(): Action
    {
        return static::base('tutup_krs', 'Tutup KRS', 'heroicon-o-lock-closed', 'warning', TahunAkademikStatus::KrsTutup)
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalContent(view('filament.modals.impact-list', ['items' => [
                'Mengunci seluruh perubahan KRS mahasiswa',
                'Mengunci jadwal kelas berdasarkan KRS final',
                'Membuka jalur ke tahap Perkuliahan',
            ]]))
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'tutupKrs', 'KRS berhasil ditutup'));
    }

    public static function mulaiPerkuliahan(): Action
    {
        return static::base('mulai_perkuliahan', 'Mulai Perkuliahan', 'heroicon-o-academic-cap', 'info', TahunAkademikStatus::Perkuliahan)
            ->modalDescription('Menandai dimulainya periode perkuliahan aktif untuk semester ini.')
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'mulaiPerkuliahan', 'Periode perkuliahan dimulai'));
    }

    public static function mulaiInputNilai(): Action
    {
        return static::base('mulai_input_nilai', 'Mulai Input Nilai', 'heroicon-o-clipboard-document-list', 'warning', TahunAkademikStatus::InputNilai)
            ->modalDescription('Dosen akan dapat menginput nilai mahasiswa untuk semua kelas pada semester ini.')
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'mulaiInputNilai', 'Periode input nilai dibuka'));
    }

    public static function lockNilai(): Action
    {
        return static::base('lock_nilai', 'Lock Nilai', 'heroicon-o-lock-closed', 'danger', TahunAkademikStatus::NilaiTerkunci)
            ->modalDescription('Dosen tidak akan bisa mengubah nilai lagi setelah dikunci. Pastikan seluruh nilai sudah masuk.')
            ->modalSubmitActionLabel('Ya, Kunci Nilai')
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'lockNilai', 'Nilai berhasil dikunci'));
    }

    public static function publishNilai(): Action
    {
        return static::base('publish_nilai', 'Publish Nilai', 'heroicon-o-megaphone', 'success', TahunAkademikStatus::NilaiPublish)
            ->modalDescription(fn(RefTahunAkademik $record) => sprintf(
                '%s KHS akan diterbitkan dan dapat dilihat oleh mahasiswa. Tindakan ini tidak dapat dibatalkan.',
                number_format($record->statistik()['mahasiswa_aktif'] ?? 0)
            ))
            ->modalSubmitActionLabel('Ya, Publish Nilai')
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'publishNilai', 'Nilai berhasil dipublish'));
    }

    public static function tutupSemester(): Action
    {
        return static::base('tutup_semester', 'Tutup Semester', 'heroicon-o-check-circle', 'gray', TahunAkademikStatus::Selesai)
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalContent(view('filament.modals.impact-list', ['items' => [
                'Mengunci KRS secara permanen',
                'Menghitung IPS seluruh mahasiswa',
                'Menghitung IPK seluruh mahasiswa',
                'Membuat riwayat akademik mahasiswa',
            ]]))
            ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
            ->modalSubmitActionLabel('Ya, Tutup Semester')
            ->action(fn(RefTahunAkademik $record) => static::run($record, 'tutupSemester', 'Semester berhasil ditutup'));
    }

    /** Dipakai di header View page — hanya render aksi yang valid untuk status saat ini. */
    public static function all(): array
    {
        return [
            static::bukaKrs(),
            static::tutupKrs(),
            static::mulaiPerkuliahan(),
            static::mulaiInputNilai(),
            static::lockNilai(),
            static::publishNilai(),
            static::tutupSemester(),
        ];
    }

    protected static function base(
        string $name,
        string $label,
        string $icon,
        string $color,
        TahunAkademikStatus $target
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->modalWidth(Width::Medium)
            ->modalHeading("{$label} — Konfirmasi")
            ->requiresConfirmation()
            ->visible(fn(RefTahunAkademik $record) => $record->status->canTransitionTo($target));
    }

    /** Bungkus pemanggilan method model dengan notifikasi sukses/gagal yang konsisten. */
    protected static function run(RefTahunAkademik $record, string $method, string $successMessage): void
    {
        try {
            $record->{$method}();

            Notification::make()->title($successMessage)->success()->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Aksi gagal diproses')
                ->body('Terjadi kesalahan, silakan coba lagi atau hubungi administrator sistem.')
                ->danger()
                ->send();
        }
    }
}
