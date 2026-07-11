<?php

namespace App\Filament\Resources\DispensasiAkademiks\Tables;

use App\Models\DispensasiAkademik;
use App\Models\DispensasiAkademikLog;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DispensasiAkademiksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge(),
                TextColumn::make('berlaku_mulai')
                    ->label('Berlaku Mulai')
                    ->date('d M Y'),
                TextColumn::make('berlaku_sampai')
                    ->label('Berlaku Sampai')
                    ->date('d M Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'DRAFT' => 'gray',
                        'AKTIF' => 'success',
                        'EXPIRED' => 'warning',
                        'DIBATALKAN' => 'danger',
                        default => 'primary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'AKTIF' => 'Aktif',
                        'EXPIRED' => 'Expired',
                        'DIBATALKAN' => 'Dibatalkan',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn(DispensasiAkademik $record) => $record->status === 'DRAFT'),
                    // Aksi: Setujui Dispensasi
                    Action::make('setujui')
                        ->label('Setujui (Aktifkan)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(DispensasiAkademik $record) => $record->status === 'DRAFT')
                        ->requiresConfirmation()
                        ->action(function (DispensasiAkademik $record) {
                            self::logAndProcessAction($record, 'DISETUJUI', [
                                'status' => 'AKTIF',
                                'disetujui_oleh' => Auth::id(),
                                'disetujui_pada' => now(),
                            ], 'Dispensasi disetujui dan diaktifkan.');
                        }),

                    // Aksi: Batalkan Dispensasi
                    Action::make('batalkan')
                        ->label('Batalkan Dispensasi')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn(DispensasiAkademik $record) => in_array($record->status, ['DRAFT', 'AKTIF']))
                        ->schema([
                            Textarea::make('alasan_batal')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(function (array $data, DispensasiAkademik $record) {
                            self::logAndProcessAction($record, 'DIBATALKAN', [
                                'status' => 'DIBATALKAN',
                            ], $data['alasan_batal']);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => false),
                ]),
            ]);
    }
    /**
     * Helper mutasi state machine dan mencatat audit trail secara atomik
     */
    private static function logAndProcessAction(DispensasiAkademik $record, string $aksi, array $updates, string $catatan): void
    {
        DB::transaction(function () use ($record, $aksi, $updates, $catatan) {
            $beforeData = $record->toArray();

            $record->update($updates);

            DispensasiAkademikLog::create([
                'dispensasi_id' => $record->id,
                'aksi' => $aksi,
                'dilakukan_oleh' => Auth::id(),
                'before_data' => $beforeData,
                'after_data' => $record->fresh()->toArray(),
                'catatan' => $catatan,
            ]);
        });
    }
}
