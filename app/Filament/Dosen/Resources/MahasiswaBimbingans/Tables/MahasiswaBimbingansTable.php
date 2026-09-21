<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables;

use App\Enums\KrsStatusEnum;
use App\Services\Akademik\KrsApprovalService;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
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
                    ->state(fn(Model $record) => $record->statusRisiko)
                    ->color(fn($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn($state) => $state?->getIcon()),

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
                    // PENTING: default dihapus supaya mahasiswa yang sudah
                    // DISETUJUI / DITOLAK tetap muncul saat halaman dibuka.
                    // Sebelumnya ->default(KrsStatusEnum::DIAJUKAN->value)
                    // membuat mereka ke-filter keluar dari list secara diam-diam.
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('krs', function ($q) use ($data) {
                            $q->where('status_krs', $data['value']);
                        });
                    }),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                // ViewAction bawaan dihapus — sudah tercover oleh review_krs
                // yang kini menampilkan detail untuk semua status KRS.
                self::makeReviewAction(),
            ]);
    }

    /**
     * Action untuk melihat detail KRS.
     * - Status DIAJUKAN  -> mode "Review" (bisa Setujui / Tolak)
     * - Status lainnya   -> mode "Lihat Detail" (read-only)
     */
    protected static function makeReviewAction(): Action
    {
        return Action::make('review_krs')
            ->label(fn(Model $record) => $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'Review KRS'
                : 'Lihat Detail KRS')
            ->icon(fn(Model $record) => $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'heroicon-o-document-magnifying-glass'
                : 'heroicon-o-eye')
            ->color(fn(Model $record) => $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'warning'
                : 'gray')
            ->slideOver()
            // PENTING: sebelumnya hanya visible untuk status DIAJUKAN.
            // Sekarang tampil selama mahasiswa punya data KRS, apa pun statusnya,
            // supaya KRS yang sudah disetujui/ditolak tetap bisa dibuka detailnya.
            ->visible(fn(Model $record) => $record->krs->first()?->status_krs !== null)
            // Judul slide-over menyertakan nama mahasiswa, supaya jelas detail
            // ini milik siapa saat dosen membuka beberapa berurutan.
            ->modalHeading(fn(Model $record) => ($record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                ? 'Review KRS — '
                : 'Detail KRS — ') . ($record->person->nama_lengkap ?? $record->nim))
            ->modalContent(function (Model $record, KrsValidationService $validationService) {
                $krs = $record->krs->first();
                $activeTa = \App\Models\RefTahunAkademik::where('is_active', 1)->first();
                $krs->loadMissing(['details.jadwalKuliah.mataKuliah', 'details.jadwalKuliah.dosenPengampu.person']);
                $hasilValidasi = $validationService->runAllValidations($record, $krs, $activeTa);

                // TODO: sesuaikan nama kolom berikut dengan skema tabel `krs`
                // yang sebenarnya (mis. bisa jadi catatan_dosen/alasan_penolakan
                // sudah ada, atau perlu ditambahkan lewat migration baru).
                // Ini untuk menampilkan riwayat keputusan saat KRS sudah final.
                $catatanTersimpan = match ($krs->status_krs) {
                    KrsStatusEnum::DISETUJUI => $krs->catatan_dosen ?? null,
                    KrsStatusEnum::DITOLAK => $krs->alasan_penolakan ?? null,
                    default => null,
                };

                return view('filament.dosen.components.review-krs-modal', [
                    'krs' => $krs,
                    'mahasiswa' => $record,
                    'hasilValidasi' => $hasilValidasi,
                    'statusRisiko' => $record->statusRisiko,
                    'totalTunggakan' => $record->totalTunggakan(),
                    'riwayatIpk' => $record->riwayatStatus,
                    // Baru: riwayat catatan/alasan dari keputusan sebelumnya.
                    // Perlu ditambahkan ke blade view review-krs-modal.blade.php,
                    // misal ditampilkan di atas daftar mata kuliah sebagai alert box.
                    'catatanTersimpan' => $catatanTersimpan,
                    'direviewPada' => $krs->reviewed_at ?? $krs->updated_at,
                ]);
            })
            // Form catatan hanya relevan saat KRS masih bisa diproses (DIAJUKAN)
            ->schema(fn(Model $record) => $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                ? [
                    Textarea::make('catatan_dosen')
                        ->label('Catatan Dosen Wali')
                        ->placeholder('Isi catatan opsional jika menyetujui, atau alasan wajib jika menolak.')
                        ->rows(3),
                ]
                : [])
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
                            if ($e instanceof \Filament\Support\Exceptions\Halt) {
                                throw $e;
                            }

                            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
                            return;
                        }
                        $action->cancel();
                    }),

                Action::make('reject')
                    ->label('Tolak KRS')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(
                        fn(Model $record) =>
                        $record->krs->first()?->status_krs === KrsStatusEnum::DIAJUKAN
                    )
                    ->schema([
                        Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->placeholder('Jelaskan alasan kenapa KRS mahasiswa ini ditolak...')
                            ->required()
                            ->rows(3),
                    ])
                    ->modalHeading('Tolak Pengajuan KRS')
                    ->modalDescription('Apakah Anda yakin ingin menolak KRS ini?')
                    ->modalSubmitActionLabel('Ya, Tolak KRS')
                    ->action(function (array $data, Model $record, KrsApprovalService $approvalService) {
                        try {
                            $krs = $record->krs->first();
                            $approvalService->reject($krs, $data['alasan_penolakan']);

                            if ($userAkun = $record->akunUser()) {
                                $userAkun->notify(new \App\Notifications\KrsStatusNotification(
                                    status: 'DITOLAK',
                                    catatan: $data['alasan_penolakan'],
                                    tahunAkademik: $krs->tahunAkademik?->nama_tahun,
                                ));
                            }

                            Notification::make()->title('KRS berhasil ditolak')->success()->send();

                            throw new Halt();
                        } catch (\Throwable $e) {
                            if ($e instanceof Halt) {
                                throw $e;
                            }

                            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}