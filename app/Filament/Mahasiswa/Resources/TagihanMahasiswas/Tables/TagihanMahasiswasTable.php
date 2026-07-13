<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Tables;

use App\Enums\StatusVerifikasiPembayaran;
use App\Models\BankKampus;
use App\Services\Pembayaran\Channels\MahasiswaUploadChannel;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagihanMahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->defaultSort('tahunAkademik.kode_tahun', 'desc')

            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Semester / Periode')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('kode_transaksi')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable()
                    ->description(fn($record) => $record->deskripsi),

                TextColumn::make('total_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR')
                    ->alignment('right'),

                TextColumn::make('total_bayar')
                    ->label('Telah Dibayar')
                    ->money('IDR')
                    ->color('success')
                    ->alignment('right'),

                // Kolom Kalkulatif: Sisa Tunggakan
                TextColumn::make('sisa_tunggakan')
                    ->label('Sisa Tunggakan')
                    ->money('IDR')
                    ->state(fn($record) => max(0, $record->total_tagihan - $record->total_bayar))
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight(fn($state) => $state > 0 ? 'bold' : 'normal')
                    ->alignment('right'),

                TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'LUNAS' => 'success',
                        'CICIL' => 'warning',
                        'BELUM' => 'danger',
                        default => 'gray',
                    }),
            ])->stackedOnMobile()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Rincian Biaya'),

                    // Aksi 2: Tombol Cerdas Upload Bukti Bayar
                    Action::make('upload_bukti')
                        ->label('Konfirmasi Bayar')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->visible(fn($record) => $record->status_bayar !== 'LUNAS')
                        ->disabled(function ($record) {
                            return \App\Models\PembayaranMahasiswa::where('tagihan_id', $record->id)
                                ->where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)
                                ->exists();
                        })

                        ->tooltip(function ($record) {
                            $adaPending = \App\Models\PembayaranMahasiswa::where('tagihan_id', $record->id)
                                ->where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)
                                ->exists();

                            return $adaPending ? 'Harap tunggu, ada bukti pembayaran Anda yang sedang diverifikasi Admin.' : 'Klik untuk upload bukti transfer';
                        })
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('nominal_bayar')
                                    ->label('Nominal Yang Dibayarkan (Rp)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(fn($record) => ($record->total_tagihan - $record->total_bayar))
                                    ->helperText(fn($record) => 'Maksimal yang dapat dibayarkan adalah Rp ' . number_format($record->total_tagihan - $record->total_bayar, 0, ',', '.'))
                                    ->placeholder('Masukkan jumlah pembayaran...'),
                                DateTimePicker::make('waktu_transfer')
                                    ->label('Tanggal & Waktu Transfer')
                                    ->default(now())
                                    ->required(),
                            ]),
                            Select::make('bank_tujuan')
                                ->label('Rekening Bank Kampus Tujuan')
                                ->required()
                                ->options(function () {
                                    return BankKampus::where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($bank) {
                                            $formatTampilan = "{$bank->nama_bank} ({$bank->atas_nama}) - {$bank->no_rekening}";
                                            return [$formatTampilan => $formatTampilan];
                                        });
                                }),

                            FileUpload::make('file_bukti')
                                ->label('Upload Foto/Scan Bukti Transfer')
                                ->image()
                                ->maxSize(2048)
                                ->directory('bukti-pembayaran-mahasiswa')
                                ->required(),

                            Textarea::make('catatan')
                                ->label('Catatan Tambahan (Opsional)')
                                ->placeholder('Misal: Pembayaran cicilan SPP ke-2')
                                ->rows(2),
                        ])
                        ->action(function (array $data, $record) {
                            // 1. Rangkai data mentah menjadi payload seragam
                            $payload = [
                                'tagihan_id'          => $record->id,
                                'nominal_bayar'       => $data['nominal_bayar'],
                                'tanggal_bayar'       => $data['waktu_transfer'],
                                'bukti_bayar_path'    => $data['file_bukti'],
                                'keterangan_pengirim' => "Bank Tujuan: {$data['bank_tujuan']} | Catatan: " . ($data['catatan'] ?? '-'),
                            ];

                            // 2. Panggil Service/Adapter (Satu baris, tanpa transaksi DB manual!)
                            app(MahasiswaUploadChannel::class)->process($payload);

                            // 3. Kirim notifikasi sukses
                            Notification::make()
                                ->success()
                                ->title('Bukti Terkirim')
                                ->body('Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi Staf Keuangan.')
                                ->send();
                        })
                ])
            ]);
    }
}
