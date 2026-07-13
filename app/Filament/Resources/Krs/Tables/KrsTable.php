<?php

namespace App\Filament\Resources\Krs\Tables;

use App\Enums\KrsStatusEnum;
use App\Models\Krs;
use App\Models\KrsStatusLog;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIM berhasil disalin'),

                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->description(function (Krs $record): string {
                        $prodi = $record->mahasiswa->prodi->nama_prodi ?? 'Prodi tidak ditemukan';
                        $kelas = $record->mahasiswa->kelas->first()?->nama_kelas ?? 'Belum ada kelas';
                        return $prodi . ' • Kelas: ' . $kelas;
                    })
                    ->wrap(),

                TextColumn::make('total_sks_diambil')
                    ->label('SKS')
                    ->numeric()
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
                IconColumn::make('status_keuangan')
                    ->label('Keuangan')
                    ->boolean()
                    ->getStateUsing(function (Krs $record): bool {
                        // Cek apakah ada tagihan di semester/tahun akademik KRS ini yang BELUM LUNAS
                        $adaTunggakanSemesterIni = $record->mahasiswa?->tagihanMahasiswas()
                            ->where('tahun_akademik_id', $record->tahun_akademik_id) // Kunci ke tahun akademik KRS
                            ->where('status_bayar', '!=', 'LUNAS')
                            ->exists();

                        // Jika tidak ada tunggakan, berarti keuangan aman (true)
                        return !$adaTunggakanSemesterIni;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->tooltip(fn(Krs $record) => 'Dicek otomatis berdasarkan tagihan semester ini')
                    ->alignCenter(),
                TextColumn::make('status_krs')
                    ->label('Status KRS')
                    ->badge()
                    ->color(fn(KrsStatusEnum $state) => $state->getColor())
                    ->formatStateUsing(fn(KrsStatusEnum $state) => $state->getLabel()),

                TextColumn::make('dosen_wali')
                    ->label('Dosen Wali')
                    ->getStateUsing(function (Krs $record): string {
                        // 1. Ambil kelas pertama mahasiswa
                        $kelas = $record->mahasiswa?->kelas?->first();
                        if (!$kelas) return 'Belum Masuk Kelas';

                        // 2. Ambil dosen wali utama (is_primary = 1)
                        $dosenWali = $kelas->dosenWali?->where('pivot.is_primary', 1)->first()
                            ?? $kelas->dosenWali?->first();

                        // 3. Panggil accessor 'nama_dengan_gelar' dari model RefPerson
                        return $dosenWali?->person?->nama_dengan_gelar ?? 'Belum di-set';
                    })
                    ->description(function (Krs $record): ?string {
                        $kelas = $record->mahasiswa?->kelas?->first();
                        if (!$kelas) return null;

                        $dosenWali = $kelas->dosenWali?->where('pivot.is_primary', 1)->first()
                            ?? $kelas->dosenWali?->first();
                        return $dosenWali?->nidn ? 'NIDN: ' . $dosenWali->nidn : null;
                    })
                    // Kolom pencarian tetap diarahkan ke 'nama_lengkap' di database agar SQL LIKE bekerja
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('mahasiswa.kelas.dosenWali.person', function ($q) use ($search) {
                            $q->where('nama_lengkap', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(),
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
                    BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $berhasil = 0;
                            foreach ($records as $record) {
                                if ($record->status_krs === 'DIAJUKAN' && $record->is_financial_verified) {
                                    self::logAndProcessAction($record, 'DISETUJUI', [
                                        'status_krs' => 'DISETUJUI',
                                        'disetujui_oleh' => Auth::id(),
                                        'disetujui_pada' => now(),
                                    ], 'KRS disetujui secara massal oleh Admin.');
                                    $berhasil++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title("Berhasil menyetujui $berhasil KRS")
                                ->success()
                                ->send();
                        })
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
