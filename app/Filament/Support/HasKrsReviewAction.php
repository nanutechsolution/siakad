<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Enums\KrsStatusEnum;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Services\Akademik\KrsApprovalService;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Slide-over review KRS yang dipakai bersama oleh:
 *  - panel Dosen (MahasiswaBimbingansTable, record = Mahasiswa)
 *  - panel Admin (KrsTable, record = Krs)
 *
 * Semua closure menerima Mahasiswa|Krs lalu dinormalisasi lewat
 * resolveKrs(), supaya satu definisi bisa dipakai dua panel tanpa
 * duplikasi. Approval selalu lewat KrsApprovalService yang memanggil
 * KrsPolicy::approve/reject server-side.
 */
trait HasKrsReviewAction
{
    protected static function makeKrsReviewAction(): Action
    {
        return Action::make('review_krs')
            ->label(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'Review KRS'
                : 'Lihat Detail KRS')
            ->icon(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'heroicon-o-document-magnifying-glass'
                : 'heroicon-o-eye')
            ->color(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'warning'
                : 'gray')
            ->slideOver()
            ->visible(fn(Mahasiswa|Krs $record) => self::krsOf($record) !== null)
            ->modalHeading(fn(Mahasiswa|Krs $record) => sprintf(
                '%s — %s',
                self::krsOf($record)?->status_krs === KrsStatusEnum::DIAJUKAN ? 'Review KRS' : 'Detail KRS',
                self::namaMahasiswaOf($record),
            ))
            ->modalContent(function (Mahasiswa|Krs $record, KrsValidationService $validationService) {
                $krs = self::resolveKrs($record);
                $mahasiswa = $krs->mahasiswa;
                $ta = $krs->tahunAkademik;

                $krs->loadMissing([
                    'mahasiswa.person',
                    'mahasiswa.prodi',
                    'mahasiswa.angkatan',
                    'mahasiswa.riwayatStatus',
                    'details.jadwalKuliah.mataKuliah',
                    'details.jadwalKuliah.ruang',
                    'details.jadwalKuliah.dosenPengampu.person',
                    'tahunAkademik',
                ]);

                return view('filament.components.review-krs-modal', [
                    'krs' => $krs,
                    'mahasiswa' => $mahasiswa,
                    'hasilValidasi' => ($mahasiswa && $ta)
                        ? $validationService->runAllValidations($mahasiswa, $krs, $ta)
                        : [],
                    'statusRisiko' => $mahasiswa?->statusRisiko,
                    'totalTunggakan' => $mahasiswa?->totalTunggakan() ?? 0,
                    'riwayatIpk' => $mahasiswa?->riwayatStatus ?? collect(),
                    // Satu-satunya kolom catatan pada tabel krs adalah catatan_admin.
                    'catatanTersimpan' => $krs->catatan_admin,
                    'direviewPada' => $krs->disetujui_pada ?? $krs->ditolak_pada,
                ]);
            })
            // Form catatan sengaja TIDAK ditaruh di slide-over ini: state
            // review dipakai oleh footer actions lain. Catatan diisi pada
            // masing-masing dialog Setujui/Tolak.
            ->schema([])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->extraModalFooterActions(fn(Action $action) => [
                static::makeKrsApproveAction()->cancelParentActions(),
                static::makeKrsRejectAction()->cancelParentActions(),
            ]);
    }

    protected static function makeKrsApproveAction(): Action
    {
        return Action::make('approve')
            ->label('Setujui KRS')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            // authorize lewat closure supaya record Mahasiswa diterjemahkan
            // dulu ke KRS — KrsPolicy::approve(Krs) tidak bisa menerima Mahasiswa.
            ->authorize(fn(Mahasiswa|Krs $record) => ($krs = self::krsOf($record)) !== null
                && (bool) Auth::user()?->can('approve', $krs))
            ->visible(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->status_krs === KrsStatusEnum::DIAJUKAN)
            ->disabled(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->is_financial_verified !== true)
            ->tooltip(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->is_financial_verified
                ? 'Setujui KRS ini'
                : 'Belum dapat disetujui: verifikasi keuangan belum lolos. Gunakan Override Keuangan bila memang didisposisikan.')
            ->requiresConfirmation()
            ->modalDescription('Pastikan hasil validasi KRS di atas sudah ditinjau sebelum menyetujui.')
            ->schema([
                Textarea::make('catatan_admin')
                    ->label('Catatan untuk mahasiswa')
                    ->placeholder('Opsional — misal syarat tambahan yang harus dipenuhi mahasiswa.')
                    ->rows(3),
            ])
            ->action(function (array $data, Mahasiswa|Krs $record, KrsApprovalService $service): void {
                try {
                    $krs = self::resolveKrs($record);
                    $service->approve($krs, $data['catatan_admin'] ?? null);
                    static::notifyKrsApplicant($krs, 'DISETUJUI', $data['catatan_admin'] ?? null);
                    Notification::make()->title('KRS berhasil disetujui')->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('Gagal menyetujui KRS')->body($e->getMessage())->danger()->send();
                }
            });
    }

    protected static function makeKrsRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Tolak KRS')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->authorize(fn(Mahasiswa|Krs $record) => ($krs = self::krsOf($record)) !== null
                && (bool) Auth::user()?->can('reject', $krs))
            ->visible(fn(Mahasiswa|Krs $record) => self::krsOf($record)?->status_krs === KrsStatusEnum::DIAJUKAN)
            ->modalHeading('Tolak Pengajuan KRS')
            ->modalDescription('Alasan penolakan wajib diisi dan akan dikirim ke mahasiswa.')
            ->modalSubmitActionLabel('Ya, Tolak KRS')
            ->schema([
                Textarea::make('catatan_admin')
                    ->label('Alasan Penolakan')
                    ->placeholder('Jelaskan alasan kenapa KRS mahasiswa ini ditolak...')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data, Mahasiswa|Krs $record, KrsApprovalService $service): void {
                try {
                    $krs = self::resolveKrs($record);
                    $service->reject($krs, $data['catatan_admin']);
                    static::notifyKrsApplicant($krs, 'DITOLAK', $data['catatan_admin']);
                    Notification::make()->title('KRS berhasil ditolak')->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('Gagal menolak KRS')->body($e->getMessage())->danger()->send();
                }
            });
    }

    /**
     * Ambil KRS periode aktif milik record, tanpa melempar exception.
     * Dipakai oleh closure visible/label yang boleh tidak menemukan KRS.
     */
    protected static function krsOf(Mahasiswa|Krs $record): ?Krs
    {
        if ($record instanceof Krs) {
            return $record;
        }

        $activeTaId = \App\Models\RefTahunAkademik::where('is_active', 1)->value('id');

        return $record->krs
            ->when($activeTaId, fn($q) => $q->where('tahun_akademik_id', $activeTaId))
            ->first();
    }

    protected static function resolveKrs(Mahasiswa|Krs $record): Krs
    {
        $krs = self::krsOf($record);

        if (! $krs) {
            throw new \RuntimeException('Mahasiswa ini tidak memiliki KRS pada periode aktif.');
        }

        return $krs;
    }

    protected static function namaMahasiswaOf(Mahasiswa|Krs $record): string
    {
        $mahasiswa = $record instanceof Krs ? $record->mahasiswa : $record;

        return $mahasiswa?->person?->nama_lengkap ?? $mahasiswa?->nim ?? '-';
    }

    protected static function notifyKrsApplicant(Krs $krs, string $status, ?string $catatan): void
    {
        $akun = $krs->mahasiswa?->akunUser();

        if ($akun) {
            $akun->notify(new \App\Notifications\KrsStatusNotification(
                status: $status,
                catatan: $catatan,
                tahunAkademik: $krs->tahunAkademik?->nama_tahun,
            ));
        }
    }
}
