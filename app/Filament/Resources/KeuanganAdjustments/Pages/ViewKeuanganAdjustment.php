<?php

namespace App\Filament\Resources\KeuanganAdjustments\Pages;

use App\Enums\Keuangan\StatusAdjustment;
use App\Exceptions\Keuangan\AdjustmentException;
use App\Filament\Resources\KeuanganAdjustments\KeuanganAdjustmentResource;
use App\Models\KeuanganAdjustment;
use App\Services\Keuangan\AdjustmentPostingService;
use App\Services\Keuangan\AdjustmentStateMachine;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewKeuanganAdjustment extends ViewRecord
{
    protected static string $resource = KeuanganAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn(KeuanganAdjustment $record): bool => $record->status === StatusAdjustment::DRAFT),

            // 1. AJUKAN (DRAFT -> DIAJUKAN)
            Action::make('ajukan')
                ->label('Ajukan Persetujuan')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Adjustment Keuangan')
                ->modalDescription('Apakah Anda yakin ingin mengajukan penyesuaian ini? Setelah diajukan, data tidak dapat diedit kembali.')
                ->visible(fn(KeuanganAdjustment $record): bool => $record->status === StatusAdjustment::DRAFT && Auth::user()->can('SubmitKeuanganAdjustment'))
                ->action(function (KeuanganAdjustment $record, Action $action) {
                    try {
                        app(AdjustmentStateMachine::class)->assertCanTransition($record, StatusAdjustment::DIAJUKAN, Auth::user());

                        $record->update([
                            'status' => StatusAdjustment::DIAJUKAN,
                            'diajukan_oleh' => Auth::id(),
                            'diajukan_at' => now(),
                        ]);

                        Notification::make()->success()->title('Berhasil Diajukan')->send();
                    } catch (AdjustmentException $e) {
                        Notification::make()->danger()->title('Gagal Mengajukan')->body($e->getMessage())->send();
                        $action->halt();
                    }
                }),

            // 2. SETUJUI (DIAJUKAN -> DISETUJUI)
            Action::make('setujui')
                ->label('Setujui Adjustment')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn(KeuanganAdjustment $record): bool => $record->status === StatusAdjustment::DIAJUKAN && Auth::user()->can('ApproveKeuanganAdjustment'))
                ->action(function (KeuanganAdjustment $record, Action $action) {
                    try {
                        app(AdjustmentStateMachine::class)->assertCanTransition($record, StatusAdjustment::DISETUJUI, Auth::user());

                        $record->update([
                            'status' => StatusAdjustment::DISETUJUI,
                            'disetujui_oleh' => Auth::id(),
                            'disetujui_at' => now(),
                        ]);

                        Notification::make()->success()->title('Adjustment Disetujui')->send();
                    } catch (AdjustmentException $e) {
                        Notification::make()->danger()->title('Persetujuan Ditolak (SoD)')->body($e->getMessage())->persistent()->send();
                        $action->halt();
                    }
                }),

            // 3. TOLAK (DIAJUKAN -> DITOLAK)
            Action::make('tolak')
                ->label('Tolak')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('catatan_approval')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->minLength(10),
                ])
                ->visible(fn(KeuanganAdjustment $record): bool => $record->status === StatusAdjustment::DIAJUKAN && Auth::user()->can('ApproveKeuanganAdjustment'))
                ->action(function (KeuanganAdjustment $record, array $data, Action $action) {
                    try {
                        app(AdjustmentStateMachine::class)->assertCanTransition($record, StatusAdjustment::DITOLAK, Auth::user());

                        $record->update([
                            'status' => StatusAdjustment::DITOLAK,
                            'disetujui_oleh' => Auth::id(), // Reviewer yang menolak
                            'disetujui_at' => now(),
                            'catatan_approval' => $data['catatan_approval'],
                        ]);

                        Notification::make()->success()->title('Adjustment Ditolak')->send();
                    } catch (AdjustmentException $e) {
                        Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        $action->halt();
                    }
                }),

            // 4. POSTING (DISETUJUI -> DIPOSTING)
            Action::make('posting')
                ->label('Posting ke Ledger')
                ->icon('heroicon-o-document-check')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Posting Penyesuaian')
                ->modalDescription('Proses ini akan mengeksekusi perubahan pada saldo tagihan dan mencatatnya di buku besar (General Ledger). Proses ini bersifat final.')
                ->visible(fn(KeuanganAdjustment $record): bool => $record->status === StatusAdjustment::DISETUJUI && Auth::user()->can('PostKeuanganAdjustment'))
                ->action(function (KeuanganAdjustment $record, Action $action) {
                    try {
                        app(AdjustmentPostingService::class)->posting($record, Auth::user());
                        Notification::make()->success()->title('Berhasil Diposting')->body('Saldo tagihan dan ledger telah diperbarui.')->send();
                    } catch (AdjustmentException $e) {
                        Notification::make()->danger()->title('Gagal Posting')->body($e->getMessage())->persistent()->send();
                        $action->halt();
                    }
                }),

            // 5. BATALKAN SEBELUM POSTING
            Action::make('batalkan_pengajuan')
                ->label('Batalkan Pengajuan')
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('alasan_pembatalan')->label('Alasan Pembatalan')->required(),
                ])
                ->visible(fn(KeuanganAdjustment $record): bool => in_array($record->status, [StatusAdjustment::DRAFT, StatusAdjustment::DIAJUKAN]) && Auth::user()->can('SubmitKeuanganAdjustment'))
                ->action(function (KeuanganAdjustment $record, array $data) {
                    $record->update([
                        'status' => StatusAdjustment::DIBATALKAN,
                        'dibatalkan_oleh' => Auth::id(),
                        'dibatalkan_at' => now(),
                        'alasan_pembatalan' => $data['alasan_pembatalan'],
                    ]);
                    Notification::make()->success()->title('Pengajuan Dibatalkan')->send();
                }),

            // 6. BUAT ADJUSTMENT PEMBALIK (SETELAH POSTING)
            Action::make('buat_pembalik')
                ->label('Buat Pembalik (Reverse)')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Buat Adjustment Pembalik')
                ->modalDescription('Tindakan ini akan membuat Draft Adjustment baru dengan nominal berlawanan untuk menetralisir adjustment ini. Anda harus mengajukan ulang draft tersebut.')
                ->visible(fn(KeuanganAdjustment $record): bool => $record->status === StatusAdjustment::DIPOSTING && Auth::user()->can('CreateKeuanganAdjustment'))
                ->action(function (KeuanganAdjustment $record) {
                    $newAdj = KeuanganAdjustment::create([
                        'tagihan_id' => $record->tagihan_id,
                        'jenis_adjustment' => $record->jenis_adjustment,
                        'nominal' => -$record->nominal, // Nominal dibalik tanda
                        'keterangan' => 'Pembalikan (Reverse) untuk adjustment nomor: ' . $record->nomor_adjustment,
                        'status' => StatusAdjustment::DRAFT,
                        'tindak_lanjut_kelebihan_bayar' => $record->tindak_lanjut_kelebihan_bayar,
                        'adjustment_pembalik_id' => $record->id,
                        'created_by' => Auth::id(),
                    ]);

                    Notification::make()->success()->title('Draft Pembalik Dibuat')->body('Silakan periksa dan ajukan draft baru ini.')->send();

                    return redirect()->to(KeuanganAdjustmentResource::getUrl('view', ['record' => $newAdj]));
                }),
        ];
    }
}
