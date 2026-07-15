<?php

namespace App\Filament\Resources\CamabaStagings\Tables;

use App\Models\PmbCamabaStaging;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CamabaStagingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('external_id')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('payload.nama_lengkap')
                    ->label('Nama Camaba')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payload.nama_prodi')
                    ->label('Prodi Diterima')
                    ->searchable(),

                TextColumn::make('payload.tahun_masuk')
                    ->label('Tahun Masuk'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'processing' => 'Diproses',
                        'processed' => 'Berhasil',
                        'failed' => 'Gagal',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'processed',
                        'danger' => 'failed',
                    ]),

                TextColumn::make('mahasiswa_id')
                    ->label('Mahasiswa')
                    ->formatStateUsing(
                        fn($state) =>
                        $state ? 'Sudah dibuat' : 'Belum'
                    )
                    ->badge()
                    ->colors([
                        'success' => fn($state) => filled($state),
                        'warning' => fn($state) => blank($state),
                    ]),

                TextColumn::make('error_log')
                    ->label('Error Terakhir')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->error_log)
                    ->placeholder('-'),

                TextColumn::make('retry_count')
                    ->label('Retry')
                    ->badge(),

                TextColumn::make('processed_at')
                    ->label('Diproses')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('reprocess')
                    ->label('Proses Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (PmbCamabaStaging $record) {
                        // Panggil kembali job proses kamu di sini
                        \App\Jobs\ProcessCamabaStaging::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Data sedang diproses ulang')
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => $record->status === 'failed'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
