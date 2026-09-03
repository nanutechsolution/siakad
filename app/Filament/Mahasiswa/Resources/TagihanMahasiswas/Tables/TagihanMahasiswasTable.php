<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Tables;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\StatusVerifikasiPembayaran;
use App\Models\BankKampus;
use App\Models\TagihanMahasiswa;
use App\Services\Pdf\PdfService;
use App\Services\Pembayaran\Channels\MahasiswaUploadChannel;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TagihanMahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                ViewAction::make()
                    ->label('Rincian Biaya'),
                Action::make('cetak-invoice')
                    ->label('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (TagihanMahasiswa $record) {
                        $document = app(PdfService::class)->generateArchived(
                            type: PdfDocumentType::INVOICE_TAGIHAN,
                            context: ['tagihan_id' => $record->id],
                            documentableType: TagihanMahasiswa::class,
                            documentableId: $record->id,
                        );
                        return app(PdfService::class)->downloadArchived($document);
                    }),

                Action::make('status_pending')
                    ->label('Sedang Diverifikasi')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->visible(function ($record) {
                        return \App\Models\PembayaranMahasiswa::where('tagihan_id', $record->id)
                            ->where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)
                            ->exists();
                    })
                    ->modalHeading('Pembayaran Sedang Diverifikasi ⏳')
                    ->modalDescription('Anda sudah mengirimkan bukti pembayaran sebelumnya. Silakan tunggu Staf Keuangan memverifikasi bukti Anda sebelum dapat melakukan pembayaran berikutnya. Anda tidak perlu mengirim ulang.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    // Mencegah modal tertutup kalau klik luar
                    ->closeModalByClickingAway(false),

                Action::make('upload_bukti')
                    ->label('Konfirmasi Bayar')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->closeModalByClickingAway(false)
                    ->visible(function ($record) {
                        $isLunas = $record->status_bayar === 'LUNAS';
                        $hasPending = \App\Models\PembayaranMahasiswa::where('tagihan_id', $record->id)
                            ->where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)
                            ->exists();
                        return !$isLunas && !$hasPending;
                    })
                    ->steps([
                        // --- STEP 1: FORM INPUT ---
                        Step::make('Data Transfer')
                            ->description('Isi sesuai struk ATM/M-Banking')
                            ->schema([
                                ToggleButtons::make('jenis_pembayaran')
                                    ->label('Pilihan Jumlah Pembayaran')
                                    ->options(function ($record) {
                                        $sisa = $record->total_tagihan - $record->total_bayar;
                                        return [
                                            'lunas' => "Bayar Lunas (Rp " . number_format($sisa, 0, ',', '.') . ")",
                                            'sebagian' => 'Nominal Lainnya / Bayar Sebagian',
                                        ];
                                    })
                                    ->colors(['lunas' => 'success', 'sebagian' => 'warning'])
                                    ->icons(['lunas' => 'heroicon-o-check-circle', 'sebagian' => 'heroicon-o-pencil-square'])
                                    ->inline()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state, $record) {
                                        $set('nominal_bayar', $state === 'lunas' ? ($record->total_tagihan - $record->total_bayar) : null);
                                    }),

                                TextInput::make('nominal_bayar')
                                    ->label('Nominal Yang Anda Transfer')
                                    ->prefix('Rp')
                                    ->extraInputAttributes([
                                        'class' => 'text-3xl font-extrabold text-primary-600 tracking-wider',
                                        'style' => 'text-align: right;'
                                    ])
                                    ->numeric()
                                    ->required()
                                    ->minValue(config('pembayaran.minimum_payment', 10000))
                                    ->live(debounce: 500)
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                    ->stripCharacters(['.', ','])
                                    ->readOnly(fn(Get $get) => $get('jenis_pembayaran') === 'lunas')
                                    ->helperText(fn(Get $get) => $get('jenis_pembayaran') === 'sebagian' ? 'Cukup ketik angkanya saja.' : 'Terisi otomatis.'),

                                // LIVE TERBILANG MENGGUNAKAN BLADE COMPONENT
                                Placeholder::make('terbilang_live')
                                    ->label('')
                                    ->hiddenLabel()
                                    ->visible(fn(Get $get) => (int) $get('nominal_bayar') > 0)
                                    ->content(function (Get $get) {
                                        $nominal = (int) $get('nominal_bayar');
                                        $terbilang = self::getFungsiTerbilang();
                                        $nominalTerbilang = trim($terbilang($nominal)) . ' Rupiah';

                                        // Lempar data ke Blade
                                        return view('components.terbilang-live', [
                                            'nominalTerbilang' => $nominalTerbilang
                                        ]);
                                    }),

                                Select::make('bank_tujuan_id')
                                    ->label('Rekening Kampus Tujuan (Sesuai Struk)')
                                    ->required()
                                    ->allowHtml()
                                    ->searchable()
                                    ->searchPrompt('Cari nama bank...')
                                    ->options(function () {
                                        return BankKampus::where('is_active', true)->get()->mapWithKeys(function ($bank) {
                                            return [$bank->id => view('components.bank-option-label', ['bank' => $bank])->render()];
                                        });
                                    }),

                                DateTimePicker::make('waktu_transfer')
                                    ->label('Tanggal & Waktu di Struk Transfer')
                                    ->default(now())
                                    ->required(),

                                FileUpload::make('file_bukti')
                                    ->label('Upload Foto/Scan Bukti Transfer')
                                    ->image()->imageEditor()->disk('public')->maxSize(2048)->directory('bukti-pembayaran')
                                    ->required(),

                                Textarea::make('catatan')
                                    ->label('Catatan Tambahan (Opsional)')
                                    ->rows(2),
                            ]),

                        // --- STEP 2: KONFIRMASI (ERROR PREVENTION) ---
                        Step::make('Konfirmasi')
                            ->description('Periksa kembali sebelum kirim')
                            ->schema([
                                // SUMMARY MENGGUNAKAN BLADE COMPONENT
                                Placeholder::make('summary')
                                    ->label('')
                                    ->hiddenLabel()
                                    ->content(function (Get $get, $record) {
                                        $sisaTagihan = $record->total_tagihan - $record->total_bayar;
                                        $nominalBayar = (int) $get('nominal_bayar');
                                        $sisaSetelahBayar = $sisaTagihan - $nominalBayar;

                                        $terbilang = self::getFungsiTerbilang();
                                        $nominalTerbilang = trim($terbilang($nominalBayar)) . ' Rupiah';

                                        $bankId = $get('bank_tujuan_id');
                                        $bank = $bankId ? BankKampus::find($bankId) : null;
                                        $namaBankLengkap = $bank
                                            ? "{$bank->nama_bank} - {$bank->no_rekening} (a.n. {$bank->atas_nama})"
                                            : '<span class="text-danger-500">Bank belum dipilih</span>';

                                        $waktuTransfer = $get('waktu_transfer')
                                            ? \Carbon\Carbon::parse($get('waktu_transfer'))->translatedFormat('d F Y, H:i')
                                            : '-';

                                        // Lempar semua variabel ke Blade
                                        return view('components.payment-summary', [
                                            'nominalBayar' => $nominalBayar,
                                            'nominalTerbilang' => $nominalTerbilang,
                                            'sisaTagihan' => $sisaTagihan,
                                            'sisaSetelahBayar' => $sisaSetelahBayar,
                                            'kelebihan' => $sisaSetelahBayar < 0 ? abs($sisaSetelahBayar) : 0,
                                            'namaBankLengkap' => $namaBankLengkap,
                                            'waktuTransfer' => $waktuTransfer,
                                            'catatan' => $get('catatan') ?: 'Tidak ada catatan',
                                        ]);
                                    })
                            ]),
                    ])
                    ->action(function (array $data, $record) {
                        // ... Logika backend sama seperti sebelumnya ...
                        $payload = [
                            'tagihan_id'       => $record->id,
                            'tagihan_type'     => 'tagihan_mahasiswa',
                            'nominal_bayar'    => (int) $data['nominal_bayar'],
                            'tanggal_bayar'    => $data['waktu_transfer'],
                            'bukti_bayar_path' => $data['file_bukti'],
                            'bank_tujuan_id'   => $data['bank_tujuan_id'],
                            'catatan'          => $data['catatan'] ?? null,
                        ];

                        app(MahasiswaUploadChannel::class)->process($payload);

                        Notification::make()
                            ->success()
                            ->title('Bukti Berhasil Terkirim! 🎉')
                            ->body('Terima kasih. Staf Keuangan akan segera memverifikasi pembayaran Anda.')
                            ->send();
                    })
            ]);
    }

    /**
     * Helper Method: Agar fungsi terbilang tidak di-copy-paste dua kali.
     * Diletakkan di level class.
     */
    private static function getFungsiTerbilang(): \Closure
    {
        $terbilang = function ($angka) use (&$terbilang) {
            $angka = abs((int)$angka);
            $baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
            $hasil = "";
            if ($angka < 12) {
                $hasil = " " . $baca[$angka];
            } else if ($angka < 20) {
                $hasil = $terbilang($angka - 10) . " Belas";
            } else if ($angka < 100) {
                $hasil = $terbilang($angka / 10) . " Puluh" . $terbilang($angka % 10);
            } else if ($angka < 200) {
                $hasil = " Seratus" . $terbilang($angka - 100);
            } else if ($angka < 1000) {
                $hasil = $terbilang($angka / 100) . " Ratus" . $terbilang($angka % 100);
            } else if ($angka < 2000) {
                $hasil = " Seribu" . $terbilang($angka - 1000);
            } else if ($angka < 1000000) {
                $hasil = $terbilang($angka / 1000) . " Ribu" . $terbilang($angka % 1000);
            } else if ($angka < 1000000000) {
                $hasil = $terbilang($angka / 1000000) . " Juta" . $terbilang($angka % 1000000);
            }
            return $hasil;
        };
        return $terbilang;
    }
}
