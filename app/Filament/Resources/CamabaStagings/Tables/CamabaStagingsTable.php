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
                    ->label('Prodi Tujuan')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'processing' => 'Diproses',
                        'processed' => 'Berhasil',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'processed',
                        'danger' => 'failed',
                    ]),

                TextColumn::make('http_code')
                    ->label('HTTP')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('error_message')
                    ->label('Pesan Error')
                    ->limit(50)
                    ->wrap()
                    ->tooltip(fn($record) => $record->error_message)
                    ->placeholder('Tidak ada error'),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
