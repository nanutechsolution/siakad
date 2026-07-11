<?php

namespace App\Filament\Resources\Krs\Tables;

use App\Models\Krs;
use App\Models\KrsStatusLog;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Periode')
                    ->sortable(),
                TextColumn::make('total_sks_diambil')
                    ->label('Total SKS')
                    ->numeric()
                    ->badge(),
                TextColumn::make('status_krs')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'DRAFT' => 'gray',
                        'DIAJUKAN' => 'warning',
                        'DISETUJUI' => 'success',
                        'DITOLAK' => 'danger',
                        'DIBATALKAN' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('dosenWali.person.nama_lengkap')
                    ->label('Dosen Wali')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun'),
                SelectFilter::make('status_krs')
                    ->label('Status KRS')
                    ->options([
                        'DRAFT' => 'Draft',
                        'DIAJUKAN' => 'Diajukan',
                        'DISETUJUI' => 'Disetujui',
                        'DITOLAK' => 'Ditolak',
                        'DIBATALKAN' => 'Dibatalkan',
                    ]),
                SelectFilter::make('dosen_wali_id')
                    ->label('Dosen Wali')
                    ->relationship('dosenWali.person', 'nama_lengkap'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn(Krs $record) => in_array($record->status_krs, ['DRAFT', 'DITOLAK'])),

                    // Aksi: Ajukan KRS
                    Action::make('ajukan')
                        ->label('Ajukan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn(Krs $record) => in_array($record->status_krs, ['DRAFT', 'DITOLAK']))
                        ->requiresConfirmation()
                        ->action(function (Krs $record) {
                            self::logAndProcessAction($record, 'DIAJUKAN', [
                                'status_krs' => 'DIAJUKAN',
                                'diajukan_at' => now(),
                            ], 'KRS diajukan untuk persetujuan.');
                        }),

                    // Aksi: Setujui KRS
                    Action::make('setujui')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(Krs $record) => $record->status_krs === 'DIAJUKAN')
                        ->requiresConfirmation()
                        ->action(function (Krs $record) {
                            self::logAndProcessAction($record, 'DISETUJUI', [
                                'status_krs' => 'DISETUJUI',
                                'disetujui_oleh' => Auth::id(),
                                'disetujui_pada' => now(),
                            ], 'KRS disetujui oleh Admin.');
                        }),

                    // Aksi: Tolak KRS
                    Action::make('tolak')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(Krs $record) => $record->status_krs === 'DIAJUKAN')
                        ->schema([
                            Textarea::make('catatan_admin')
                                ->label('Alasan Penolakan')
                                ->required(),
                        ])
                        ->action(function (array $data, Krs $record) {
                            self::logAndProcessAction($record, 'DITOLAK', [
                                'status_krs' => 'DITOLAK',
                                'ditolak_oleh' => Auth::id(),
                                'ditolak_pada' => now(),
                                'catatan_admin' => $data['catatan_admin'],
                            ], $data['catatan_admin']);
                        }),

                    // Aksi: Batalkan KRS
                    Action::make('batalkan')
                        ->label('Batalkan KRS')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->visible(fn(Krs $record) => $record->status_krs === 'DISETUJUI')
                        ->form([
                            Textarea::make('alasan_batal')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(function (array $data, Krs $record) {
                            self::logAndProcessAction($record, 'DIBATALKAN', [
                                'status_krs' => 'DIBATALKAN',
                                'catatan_admin' => $data['alasan_batal'],
                            ], $data['alasan_batal']);

                            // Trigger observer untuk mereverse isi_kelas di tabel jadwal_kuliah
                            // (Event update 'status_krs' menjadi DIBATALKAN bisa dipantau di Observer Krs.php nanti jika diperlukan untuk mass decrement)
                        }),

                    // Aksi: Override Keuangan
                    Action::make('override_keuangan')
                        ->label('Override Keuangan')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->visible(fn(Krs $record) => !$record->is_financial_verified)
                        ->form([
                            Textarea::make('financial_override_reason')
                                ->label('Alasan Override/Dispensasi Pembayaran')
                                ->required(),
                        ])
                        ->action(function (array $data, Krs $record) {
                            self::logAndProcessAction($record, 'DIUBAH_ADMIN', [
                                'is_financial_verified' => true,
                                'financial_override_by' => Auth::id(),
                                'financial_override_reason' => $data['financial_override_reason'],
                            ], 'Override validasi keuangan: ' . $data['financial_override_reason']);
                        }),

                    // Aksi: Buka Kembali KRS (Jika terkunci)
                    Action::make('buka_kembali')
                        ->label('Buka Kembali KRS')
                        ->icon('heroicon-o-lock-open')
                        ->color('warning')
                        ->visible(fn(Krs $record) => $record->status_krs === 'DISETUJUI' && $record->tahunAkademik->is_locked_krs)
                        ->form([
                            Textarea::make('alasan_buka')
                                ->label('Alasan Membuka Kembali KRS Terkunci')
                                ->required(),
                        ])
                        ->action(function (array $data, Krs $record) {
                            self::logAndProcessAction($record, 'DIBUKA_KEMBALI', [
                                'status_krs' => 'DRAFT', // Kembalikan ke Draft agar bisa diubah
                                'catatan_admin' => $data['alasan_buka'],
                            ], $data['alasan_buka']);
                        }),
                    Action::make('cetak')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(Krs $record) => route('krs.cetak', $record->id))
                        ->openUrlInNewTab(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Helper mutasi state machine dan mencatat audit trail secara atomik
     */
    private static function logAndProcessAction(Krs $record, string $aksi, array $updates, string $catatan): void
    {
        DB::transaction(function () use ($record, $aksi, $updates, $catatan) {
            $beforeData = $record->toArray();

            $record->update($updates);

            KrsStatusLog::create([
                'krs_id' => $record->id,
                'aksi' => $aksi,
                'dilakukan_oleh' => Auth::id(),
                'before_data' => $beforeData,
                'after_data' => $record->fresh()->toArray(),
                'catatan' => $catatan,
            ]);
        });
    }
}
