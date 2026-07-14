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
                TextColumn::make('external_id')->label('ID Pendaftaran')->searchable(),
                TextColumn::make('nama_lengkap')->label('Nama Camaba')->searchable(),
                TextColumn::make('prodi_tujuan')->label('Prodi'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'processed',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('created_at')->label('Diterima pada')->dateTime(),
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
