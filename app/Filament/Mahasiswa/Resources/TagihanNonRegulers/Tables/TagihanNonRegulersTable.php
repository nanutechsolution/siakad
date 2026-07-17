<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Tables;

use App\Models\TagihanNonReguler;
use App\Services\Pembayaran\Channels\MahasiswaUploadChannel;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagihanNonRegulersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_transaksi')
                    ->label('Kode Transaksi')
                    ->searchable(),

                TextColumn::make('deskripsi')
                    ->wrap(),

                TextColumn::make('total_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR'),

                TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR'),

                TextColumn::make('status_bayar')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'LUNAS' => 'success',
                        'CICIL' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('tenggat_waktu')
                    ->label('Tenggat')
                    ->date('d M Y'),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('uploadBuktiBayar')
                    ->label('Upload Bukti Bayar')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn(TagihanNonReguler $record) => $record->status_bayar !== 'LUNAS')
                    ->schema([
                        TextInput::make('nominal_bayar')
                            ->label('Nominal Dibayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0.01),

                        DatePicker::make('tanggal_bayar')
                            ->label('Tanggal Bayar')
                            ->native(false)
                            ->required()
                            ->maxDate(now()),

                        FileUpload::make('bukti_bayar_path')
                            ->label('Bukti Pembayaran')
                            ->required()
                            ->disk('public')
                            ->directory('bukti-bayar/non-reguler')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf']),

                        Textarea::make('keterangan_pengirim')
                            ->label('Keterangan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (TagihanNonReguler $record, array $data, MahasiswaUploadChannel $channel) {
                        // Satu pintu yang sama dipakai untuk tagihan semester
                        // maupun non reguler — dibedakan lewat tagihan_type
                        // (morph alias) sejak pembayaran_mahasiswas.tagihan_id
                        // dibuat polymorphic. Lihat ANALISIS-PEMBAYARAN.md.
                        $channel->process([
                            'tagihan_id' => $record->id,
                            'tagihan_type' => 'tagihan_non_reguler',
                            'nominal_bayar' => $data['nominal_bayar'],
                            'tanggal_bayar' => $data['tanggal_bayar'],
                            'bukti_bayar_path' => $data['bukti_bayar_path'],
                            'keterangan_pengirim' => $data['keterangan_pengirim'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Bukti pembayaran berhasil diunggah')
                            ->body('Menunggu verifikasi dari admin keuangan.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
