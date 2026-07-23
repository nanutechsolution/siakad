<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables;

use App\Enums\KrsStatusEnum;
use App\Services\Akademik\KrsApprovalService;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBimbingansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Prodi'),

                TextColumn::make('krs.status_krs')
                    ->label('Status KRS Aktif')
                    ->badge()
                    ->state(fn(Model $record) => $record->krs->first()?->status_krs?->value ?? 'BELUM AJUAN')
                    ->color(fn(string $state) => match ($state) {
                        KrsStatusEnum::DISETUJUI->value => KrsStatusEnum::DISETUJUI->getColor(),
                        KrsStatusEnum::DIAJUKAN->value => KrsStatusEnum::DIAJUKAN->getColor(),
                        KrsStatusEnum::DITOLAK->value => KrsStatusEnum::DITOLAK->getColor(),
                        default => 'gray',
                    }),
                TextColumn::make('status_risiko')
                    ->label('Risiko Akademik')
                    ->badge()
                    ->state(fn(Model $record) => $record->statusRisiko())
                    ->color(fn($state) => $state->getColor())
                    ->icon(fn($state) => $state->getIcon()),

                TextColumn::make('tunggakan')
                    ->label('Tunggakan')
                    ->state(fn(Model $record) => $record->totalTunggakan())
                    ->money('IDR')
                    ->color(fn(Model $record) => $record->totalTunggakan() > 0 ? 'danger' : 'success')
                    ->weight(fn(Model $record) => $record->totalTunggakan() > 0 ? 'bold' : 'normal'),
            ])
            ->filters([
                SelectFilter::make('status_krs')
                    ->label('Status Pengajuan KRS')
                    ->options([
                        KrsStatusEnum::DIAJUKAN->value => 'Menunggu Persetujuan',
                        KrsStatusEnum::DISETUJUI->value => 'Sudah Disetujui',
                        KrsStatusEnum::DITOLAK->value => 'Ditolak',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) return $query;
                        return $query->whereHas('krs', fn($q) => $q->where('status_krs', $data['value']));
                    }),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                self::makeReviewAction(),
            ]);
    }

    /**
     * Custom Action untuk Review KRS (SlideOver dengan Tombol Approve & Reject)
     */
    protected static function makeReviewAction(): Action
    {
        return Action::make('review_krs')
            ->label('Review KRS')
            ->icon('heroicon-o-document-magnifying-glass')
            ->color('warning')
            ->slideOver() // Membuka modal dari samping
            // Action ini hanya muncul jika statusnya DIAJUKAN
            ->visible(fn(Model $record) => $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN)
            // Modal Content (Akan kita buat menggunakan Infolist/View terpisah nanti)
            ->modalContent(function (Model $record, KrsValidationService $validationService) {
                $krs = $record->krs->first();
                $activeTa = \App\Models\RefTahunAkademik::where('is_active', 1)->first();
                $krs->loadMissing(['details.jadwalKuliah.mataKuliah', 'details.jadwalKuliah.dosenPengampu.person']);
                $hasilValidasi = $validationService->runAllValidations($record, $krs, $activeTa);
                return view('filament.dosen.components.review-krs-modal', [
                    'krs' => $krs,
                    'mahasiswa' => $record,
                    'hasilValidasi' => $hasilValidasi,
                    'statusRisiko' => $record->statusRisiko(),
                    'totalTunggakan' => $record->totalTunggakan(),
                    'riwayatIpk' => $record->riwayatStatus,
                ]);
            })

            // Form untuk catatan (wajib jika ditolak)
            ->schema([
                Textarea::make('catatan_dosen')
                    ->label('Catatan Dosen Wali')
                    ->placeholder('Isi catatan opsional jika menyetujui, atau alasan wajib jika menolak.')
                    ->rows(3),
            ])
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraModalFooterActions(fn(Action $action) => [
                Action::make('approve')
                    ->label('Setujui KRS')
                    ->color('success')
                    ->visible(
                        fn(Model $record) =>
                        $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                    )
                    ->cancelParentActions()
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function (array $data, Model $record, KrsApprovalService $approvalService) use ($action) {
                        try {
                            $krs = $record->krs->first();
                            $approvalService->approve($krs, $data['catatan_dosen'] ?? null);
                            if ($userAkun = $record->akunUser()) {
                                $userAkun->notify(new \App\Notifications\KrsStatusNotification(
                                    status: 'DISETUJUI',
                                    catatan: $data['catatan_dosen'] ?? null,
                                    tahunAkademik: $krs->tahunAkademik?->nama_tahun,
                                ));
                            }

                            Notification::make()->title('KRS berhasil disetujui')->success()->send();
                        } catch (\Throwable $e) {
                            // Abaikan (throw kembali) jika ini adalah exception Halt dari Filament
                            if ($e instanceof \Filament\Support\Exceptions\Halt) {
                                throw $e;
                            }

                            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
                            return; // Hentikan proses jika benar-benar gagal
                        }
                        // Tutup modal dengan aman di luar blok try-catch
                        $action->cancel();
                    }),

                // Tombol Tolak
                Action::make('reject')
                    ->label('Tolak KRS')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->close()
                    ->visible(
                        fn(Model $record) =>
                        $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                    )
                    ->action(function (array $data, Model $record, KrsApprovalService $approvalService) use ($action) {
                        if (empty(trim($data['catatan_dosen'] ?? ''))) {
                            Notification::make()->title('Catatan wajib diisi saat menolak KRS.')->warning()->send();
                            return; // Hentikan proses jika catatan kosong (jangan tutup modal)
                        }

                        try {
                            $krs = $record->krs->first();
                            $approvalService->reject($krs, $data['catatan_dosen']);

                            Notification::make()->title('KRS berhasil ditolak')->success()->send();
                        } catch (\Throwable $e) {
                            if ($e instanceof \Filament\Support\Exceptions\Halt) {
                                throw $e;
                            }

                            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
                            return; // Hentikan proses jika benar-benar gagal
                        }

                        // Tutup modal dengan aman di luar blok try-catch
                        $action->cancel();
                    }),
            ]);
    }
}
