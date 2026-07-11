<?php

namespace App\Filament\Resources\PembayaranMahasiswas\Tables;

use App\Enums\Keuangan\StatusVerifikasiKode;
use App\Models\PembayaranMahasiswa;
use App\Services\Keuangan\PembayaranVerificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PembayaranMahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tagihan.mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nominal_bayar')
                    ->label('Nominal')
                    ->money('idr'),
                TextColumn::make('statusVerifikasi.kode')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => StatusVerifikasiKode::from($state)->getColor()),
                TextColumn::make('tanggal_bayar')
                    ->label('Tgl Bayar')
                    ->dateTime(),
            ])
            ->recordActions([
                Action::make('verifikasi_valid')
                    ->label('Terima')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(PembayaranMahasiswa $record) => $record->statusVerifikasi->kode === 'PENDING')
                    ->action(fn(PembayaranMahasiswa $record) => app(PembayaranVerificationService::class)->verifikasi($record, StatusVerifikasiKode::VALID, auth()->user())),

                Action::make('verifikasi_tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->schema([
                        Textarea::make('catatan_verifikasi')->required(),
                    ])
                    ->visible(fn(PembayaranMahasiswa $record) => $record->statusVerifikasi->kode === 'PENDING')
                    ->action(fn(PembayaranMahasiswa $record, array $data) => app(PembayaranVerificationService::class)->verifikasi($record, StatusVerifikasiKode::INVALID, auth()->user(), $data['catatan_verifikasi'])),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
