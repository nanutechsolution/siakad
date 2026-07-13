<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Pages;

use App\Enums\StatusVerifikasiPembayaran;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\TagihanMahasiswaResource;
use App\Models\KeuanganSaldo;
use App\Models\KeuanganSaldoTransaction;
use App\Models\PembayaranMahasiswa;
use App\Services\Pembayaran\PembayaranAllocationService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViewTagihanMahasiswa extends ViewRecord
{
    protected static string $resource = TagihanMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bayarViaSaldo')
                ->label('Bayar via Saldo Deposit')
                ->icon('heroicon-o-wallet')
                ->color('success')
                ->visible(function () {
                    if ($this->record->status_bayar === 'LUNAS') {
                        return false;
                    }
                    $saldo = KeuanganSaldo::where('mahasiswa_id', $this->record->mahasiswa_id)->value('saldo') ?? '0.00';
                    return bccomp((string) $saldo, '0.00', 2) === 1;
                })
                ->modalWidth('xl')
                ->mountUsing(function (Schema $form) {
                    $saldo = KeuanganSaldo::where('mahasiswa_id', $this->record->mahasiswa_id)->value('saldo') ?? '0.00';
                    $sisaTagihan = bcsub((string) $this->record->total_tagihan, (string) $this->record->total_bayar, 2);

                    // Isi nominal rekomendasi dengan nilai terkecil antara sisa tagihan atau sisa saldo
                    $nominalRekomendasi = bccomp($saldo, $sisaTagihan, 2) === 1 ? $sisaTagihan : $saldo;

                    $form->fill([
                        'saldo_saat_ini' => 'Rp ' . number_format((float) $saldo, 0, ',', '.'),
                        'nominal_bayar' => $nominalRekomendasi,
                    ]);
                })
                ->schema([
                    TextInput::make('saldo_saat_ini')
                        ->label('Saldo Deposit Anda Saat Ini')
                        ->disabled(),

                    TextInput::make('nominal_bayar')
                        ->label('Nominal yang Ingin Dibayarkan')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->prefix('Rp')
                        ->rules([
                            function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    $saldo = KeuanganSaldo::where('mahasiswa_id', $this->record->mahasiswa_id)->value('saldo') ?? '0.00';
                                    if (bccomp((string) $value, (string) $saldo, 2) === 1) {
                                        $fail('Nominal pembayaran melebihi saldo deposit Anda.');
                                    }

                                    $sisaTagihan = bcsub((string) $this->record->total_tagihan, (string) $this->record->total_bayar, 2);
                                    if (bccomp((string) $value, $sisaTagihan, 2) === 1) {
                                        $fail('Nominal pembayaran melebihi sisa tunggakan tagihan ini.');
                                    }
                                };
                            },
                        ]),
                ])
                ->action(function (array $data, PembayaranAllocationService $allocationService) {
                    DB::transaction(function () use ($data, $allocationService) {
                        // Lock baris saldo untuk menghindari race condition finansial
                        $saldo = KeuanganSaldo::where('mahasiswa_id', $this->record->mahasiswa_id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        $nominal = (string) $data['nominal_bayar'];

                        // 1. Potong saldo utama mahasiswa
                        $saldo->saldo = bcsub((string) $saldo->saldo, $nominal, 2);
                        $saldo->last_updated_at = now();
                        $saldo->save();

                        // 2. Buat data pembayaran langsung DITERIMA (karena memakai dana internal bank sistem)
                        $pembayaran = PembayaranMahasiswa::create([
                            'id' => Str::uuid()->toString(),
                            'idempotency_key' => Str::uuid()->toString(),
                            'tagihan_id' => $this->record->id,
                            'nominal_bayar' => $nominal,
                            'tanggal_bayar' => now(),
                            'bank_tujuan' => 'SALDO DEPOSIT INTERNAL',
                            'bukti_bayar_path' => null,
                            'catatan' => 'Pembayaran instan via potong Saldo Deposit Mahasiswa.',
                            'status_verifikasi_id' => StatusVerifikasiPembayaran::VERIFIED,
                        ]);

                        // 3. Catat riwayat log mutasi keluar (OUT)
                        KeuanganSaldoTransaction::create([
                            'saldo_id' => $saldo->id,
                            'tipe' => 'OUT',
                            'nominal' => $nominal,
                            'referensi_id' => $pembayaran->id,
                            'keterangan' => 'Pembayaran tagihan invoice ' . $this->record->kode_transaksi,
                        ]);

                        // 4. Eksekusi alokasi pembagian dana FIFO ke tagihan detail komponen biaya
                        $allocationService->alokasikan($pembayaran);
                    });

                    Notification::make()
                        ->success()
                        ->title('Pembayaran Berhasil')
                        ->body('Tagihan Anda telah berhasil dibayar secara instan menggunakan Saldo Deposit.')
                        ->send();

                    return redirect()->to(TagihanMahasiswaResource::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('konfirmasiPembayaran')
                ->label('Konfirmasi Pembayaran Transfer')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(fn() => $this->record->status_bayar !== 'LUNAS')
                ->modalWidth('2xl')
                ->schema([
                    TextInput::make('nominal_bayar')
                        ->numeric()
                        ->required()
                        ->prefix('Rp'),

                    DateTimePicker::make('tanggal_bayar')
                        ->required()
                        ->default(now()),

                    Select::make('bank_tujuan')
                        ->required()
                        ->options([
                            'BNI' => 'BNI',
                            'BRI' => 'BRI',
                            'Mandiri' => 'Mandiri',
                            'BCA' => 'BCA',
                        ]),

                    FileUpload::make('bukti_bayar')
                        ->required()
                        ->directory('bukti-pembayaran')
                        ->image()
                        ->maxSize(4096),
                    Textarea::make('catatan'),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        PembayaranMahasiswa::create([
                            'id' => Str::uuid()->toString(),
                            'idempotency_key' => Str::uuid()->toString(),
                            'tagihan_id' => $this->record->id,
                            'nominal_bayar' => $data['nominal_bayar'],
                            'tanggal_bayar' => $data['tanggal_bayar'],
                            'bank_tujuan' => $data['bank_tujuan'],
                            'bukti_bayar_path' => $data['bukti_bayar'],
                            'catatan' => $data['catatan'],
                            'status_verifikasi_id' => StatusVerifikasiPembayaran::PENDING ?? 1,
                        ]);
                    });

                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Konfirmasi pembayaran transfer berhasil dikirim. Menunggu verifikasi admin.')
                        ->send();

                    return redirect()->to(TagihanMahasiswaResource::getUrl('view', ['record' => $this->record]));
                }),

        ];
    }
}
