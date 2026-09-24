<?php

namespace App\Filament\Resources\Krs\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Enums\KrsStatusEnum;
use App\Enums\Pdf\PdfDocumentType;
use App\Filament\Actions\Pdf\PdfDownloadAction;
use App\Filament\Support\HasKrsReviewAction;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Services\Akademik\KrsApprovalService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KrsTable
{
    use HasKrsReviewAction;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with([
                'mahasiswa.person',
                'mahasiswa.prodi',
                'tahunAkademik',
            ]))
            // Antrean terlama dulunya — supaya SLA persetujuan terlihat.
            ->defaultSort('diajukan_at', 'asc')
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Krs $record) => $record->mahasiswa?->prodi?->nama_prodi)
                    ->wrap(),

                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Periode')
                    ->sortable(),

                TextColumn::make('total_sks_diambil')
                    ->label('SKS')
                    ->numeric()
                    ->badge()
                    ->alignCenter(),

                IconColumn::make('status_keuangan')
                    ->label('Keuangan')
                    ->boolean()
                    ->getStateUsing(fn(Krs $record): bool => (bool) $record->is_financial_verified)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn(Krs $record) => $record->is_financial_verified
                        ? 'Lolos verifikasi keuangan'
                        : 'Belum lolos verifikasi keuangan'),

                TextColumn::make('status_krs')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?KrsStatusEnum $state) => $state?->getLabel() ?? '-')
                    ->color(fn(?KrsStatusEnum $state) => $state?->getColor() ?? 'gray'),

                TextColumn::make('diajukan_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Belum diajukan'),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun'),

                SelectFilter::make('status_krs')
                    ->label('Status KRS')
                    ->options(KrsStatusEnum::options()),

                // Opsi prodi dikunci ke scope user — Admin Prodi tidak pernah
                // bisa memilih (apalagi melihat) prodi di luar wewenangnya.
                SelectFilter::make('mahasiswa.prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(Auth::user()))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    static::makeKrsReviewAction('admin'),
                    ViewAction::make(),

                    EditAction::make()
                        ->visible(fn(Krs $record) => in_array($record->status_krs, [
                            KrsStatusEnum::DRAFT,
                            KrsStatusEnum::DITOLAK,
                        ], true))
                        ->authorize('update'),

                    Action::make('ajukan')
                        ->label('Ajukan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn(Krs $record) => in_array($record->status_krs, [
                            KrsStatusEnum::DRAFT,
                            KrsStatusEnum::DITOLAK,
                        ], true))
                        ->authorize('update')
                        ->requiresConfirmation()
                        ->action(fn(Krs $record) => self::ajukan($record)),

                    Action::make('buka_kembali')
                        ->label('Buka Kembali KRS')
                        ->icon('heroicon-o-lock-open')
                        ->color('warning')
                        ->visible(fn(Krs $record) => $record->status_krs === KrsStatusEnum::DISETUJUI)
                        ->authorize('cancel')
                        ->schema([
                            Textarea::make('catatan_admin')
                                ->label('Alasan Membuka Kembali KRS')
                                ->required(),
                        ])
                        ->action(fn(array $data, Krs $record) => self::bukaKembali($record, $data['catatan_admin'])),

                    Action::make('batalkan')
                        ->label('Batalkan KRS')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->visible(fn(Krs $record) => $record->status_krs === KrsStatusEnum::DISETUJUI)
                        ->authorize('cancel')
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('catatan_admin')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(fn(array $data, Krs $record) => self::cancel($record, $data['catatan_admin'])),

                    Action::make('override_keuangan')
                        ->label('Override Keuangan')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->visible(fn(Krs $record) => ! $record->is_financial_verified)
                        ->authorize('update')
                        ->schema([
                            Textarea::make('financial_override_reason')
                                ->label('Alasan Override/Dispensasi Pembayaran')
                                ->required(),
                        ])
                        ->action(fn(array $data, Krs $record) => self::overrideFinance(
                            $record,
                            $data['financial_override_reason'],
                        )),

                    PdfDownloadAction::make(
                        name: 'cetak',
                        label: 'Cetak PDF',
                        type: PdfDocumentType::KRS,
                        contextResolver: fn(Krs $record) => ['krs_id' => $record->id],
                    ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription(
                            'Setiap KRS diperiksa ulang per baris: harus berstatus Diajukan, lolos verifikasi keuangan, dan sesuai kewenangan Anda. Yang tidak memenuhi akan dilewati.',
                        )
                        ->action(function (Collection $records, KrsApprovalService $service): void {
                            $berhasil = 0;
                            $dilewati = 0;
                            $gagal = 0;
                            $user = Auth::user();

                            foreach ($records as $record) {
                                $layak = $user
                                    && $user->can('approve', $record)
                                    && $record->status_krs === KrsStatusEnum::DIAJUKAN
                                    && $record->is_financial_verified;

                                if (! $layak) {
                                    $dilewati++;
                                    continue;
                                }

                                try {
                                    $service->approve($record);
                                    $berhasil++;
                                } catch (\Throwable) {
                                    $gagal++;
                                }
                            }

                            Notification::make()
                                ->title('Proses persetujuan selesai')
                                ->body("{$berhasil} berhasil, {$dilewati} dilewati, {$gagal} gagal karena validasi atau kuota kelas.")
                                ->color($gagal > 0 || $dilewati > 0 ? 'warning' : 'success')
                                ->persistent()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    protected static function ajukan(Krs $record): void
    {
        $record->update([
            'status_krs' => KrsStatusEnum::DIAJUKAN,
            'diajukan_at' => now(),
        ]);

        DB::table('krs_status_logs')->insert([
            'krs_id' => $record->getKey(),
            'aksi' => 'DIAJUKAN',
            'dilakukan_oleh' => Auth::id(),
            'before_data' => json_encode(['status_krs' => KrsStatusEnum::DRAFT->value]),
            'after_data' => json_encode(['status_krs' => KrsStatusEnum::DIAJUKAN->value]),
            'catatan' => 'KRS diajukan untuk persetujuan.',
            'created_at' => now(),
        ]);

        Notification::make()->title('KRS diajukan untuk persetujuan')->success()->send();
    }

    protected static function bukaKembali(Krs $record, string $catatan): void
    {
        $record->update([
            'status_krs' => KrsStatusEnum::DRAFT,
            'catatan_admin' => $catatan,
        ]);

        DB::table('krs_status_logs')->insert([
            'krs_id' => $record->getKey(),
            'aksi' => 'DIBUKA_KEMBALI',
            'dilakukan_oleh' => Auth::id(),
            'before_data' => json_encode(['status_krs' => KrsStatusEnum::DISETUJUI->value]),
            'after_data' => json_encode(['status_krs' => KrsStatusEnum::DRAFT->value]),
            'catatan' => $catatan,
            'created_at' => now(),
        ]);

        Notification::make()->title('KRS dibuka kembali')->body('Status kembali ke Draft untuk direvisi.')->success()->send();
    }

    protected static function cancel(Krs $record, string $catatan): void
    {
        DB::transaction(function () use ($record, $catatan): void {
            $record->update([
                'status_krs' => KrsStatusEnum::DIBATALKAN,
                'catatan_admin' => $catatan,
            ]);

            // KRS yang disetujui sudah menambah isi_kelas saat approve
            // (observer tidak aktif), jadi pembatalan harus mengembalikannya.
            $jadwalIds = $record->details()->pluck('jadwal_kuliah_id')->filter()->values()->all();

            if ($jadwalIds !== []) {
                JadwalKuliah::query()
                    ->whereIn('id', $jadwalIds)
                    ->lockForUpdate()
                    ->decrement('isi_kelas');
            }

            DB::table('krs_status_logs')->insert([
                'krs_id' => $record->getKey(),
                'aksi' => 'DIBATALKAN',
                'dilakukan_oleh' => Auth::id(),
                'before_data' => json_encode(['status_krs' => KrsStatusEnum::DISETUJUI->value]),
                'after_data' => json_encode(['status_krs' => KrsStatusEnum::DIBATALKAN->value]),
                'catatan' => $catatan,
                'created_at' => now(),
            ]);
        });

        Notification::make()
            ->title('KRS dibatalkan')
            ->body('KRS mahasiswa tidak lagi berlaku pada periode ini.')
            ->success()
            ->send();
    }

    protected static function overrideFinance(Krs $record, string $reason): void
    {
        $record->update([
            'is_financial_verified' => true,
            'financial_override_by' => Auth::id(),
            'financial_override_reason' => $reason,
        ]);

        Notification::make()
            ->title('Verifikasi keuangan dioverride')
            ->body('KRS kini dapat diproses persetujuannya.')
            ->success()
            ->send();
    }
}
