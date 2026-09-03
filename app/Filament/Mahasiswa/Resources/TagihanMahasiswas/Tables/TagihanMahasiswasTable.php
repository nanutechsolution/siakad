<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Tables;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\StatusVerifikasiPembayaran;
use App\Models\BankKampus;
use App\Models\PembayaranMahasiswa;
use App\Models\TagihanMahasiswa;
use App\Services\Pdf\PdfService;
use App\Services\Pembayaran\Channels\MahasiswaUploadChannel;
use App\Support\Terbilang;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            ])
            ->stackedOnMobile()
            ->emptyStateHeading('Belum Ada Tagihan')
            ->emptyStateDescription('Tagihan pembayaran Anda akan muncul di sini setelah diterbitkan oleh bagian keuangan.')
            ->emptyStateIcon('heroicon-o-banknotes')
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
                        return PembayaranMahasiswa::where('tagihan_id', $record->id)
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
                    ->modalWidth('2xl')
                    // MENCEGAH MODAL TERTUTUP SAAT KLIK DI LUAR MODAL (BACKDROP)
                    ->closeModalByClickingAway(false)
                    ->visible(function ($record) {
                        $isLunas = $record->status_bayar === 'LUNAS';
                        $hasPending = PembayaranMahasiswa::where('tagihan_id', $record->id)
                            ->where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)
                            ->exists();
                        return !$isLunas && !$hasPending;
                    })
                    ->steps([
                        Step::make('Data Transfer')
                            ->description('Isi sesuai dengan struk bukti transfer Anda')
                            ->schema([
                                ToggleButtons::make('jenis_pembayaran')
                                    ->label('Pilihan Jumlah Pembayaran')
                                    ->options(function ($record) {
                                        $sisa = $record->total_tagihan - $record->total_bayar;
                                        $sisaFormat = number_format($sisa, 0, ',', '.');
                                        return [
                                            'lunas' => "Bayar Lunas (Rp {$sisaFormat})",
                                            'sebagian' => 'Nominal Lainnya / Bayar Sebagian',
                                        ];
                                    })
                                    ->colors([
                                        'lunas' => 'success',
                                        'sebagian' => 'warning',
                                    ])
                                    ->icons([
                                        'lunas' => 'heroicon-o-check-circle',
                                        'sebagian' => 'heroicon-o-pencil-square',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state, $record) {
                                        if ($state === 'lunas') {
                                            $sisa = $record->total_tagihan - $record->total_bayar;
                                            $set('nominal_bayar', $sisa);
                                        } else {
                                            $set('nominal_bayar', null);
                                        }
                                    }),

                                TextInput::make('nominal_bayar')
                                    ->label('Nominal Yang Anda Transfer')
                                    ->prefix('Rp')
                                    // 1. UX UPGRADE: Perbesar font dan rata kanan seperti layar ATM
                                    ->extraInputAttributes([
                                        'class' => 'text-3xl font-extrabold text-primary-600 dark:text-primary-400 tracking-wider',
                                        'style' => 'text-align: right;'
                                    ])
                                    ->numeric()
                                    ->required()
                                    // FIX: minimum pembayaran hanya berlaku untuk pembayaran sebagian.
                                    // Sebelumnya sisa tagihan yang lebih kecil dari nominal minimum
                                    // (mis. sisa Rp 5.000 dengan minimum Rp 10.000) membuat mode
                                    // "Bayar Lunas" gagal divalidasi padahal nominalnya sudah benar.
                                    ->minValue(fn(Get $get) => $get('jenis_pembayaran') === 'sebagian'
                                        ? config('pembayaran.minimum_payment', 10000)
                                        : 1)
                                    // 2. UX UPGRADE: Ganti onBlur menjadi debounce agar 'terbilang' muncul saat mengetik
                                    ->live(debounce: 500)
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                    ->stripCharacters(['.', ','])
                                    ->readOnly(fn(Get $get) => $get('jenis_pembayaran') === 'lunas')
                                    ->helperText(
                                        fn(Get $get) =>
                                        $get('jenis_pembayaran') === 'sebagian'
                                            ? 'Minimal Rp 10.000. Cukup ketik angkanya saja.'
                                            : 'Nominal pelunasan terisi otomatis.'
                                    ),

                                // 3. UX UPGRADE: Real-time Terbilang Feedback di bawah form input
                                TextEntry::make('terbilang_live')
                                    ->label('')
                                    ->hiddenLabel() // Sembunyikan label agar menyatu dengan field input
                                    ->visible(fn(Get $get) => (int) $get('nominal_bayar') > 0)
                                    ->state(function (Get $get) {
                                        $nominalTerbilang = Terbilang::make((int) $get('nominal_bayar'));

                                        $html = "
            <div class='-mt-4 px-4 py-3 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 rounded-lg text-primary-700 dark:text-primary-300 text-sm font-semibold shadow-inner'>
                ✓ {$nominalTerbilang}
            </div>
        ";
                                        return new HtmlString($html);
                                    }),

                                Select::make('bank_tujuan_id')
                                    ->label('Rekening Kampus Tujuan (Sesuai Struk)')
                                    ->required()
                                    ->allowHtml()
                                    ->searchable()
                                    ->searchPrompt('Cari nama bank...')
                                    // FIX: baris hasil pencarian sekarang memakai partial view yang sama
                                    // dengan daftar awal, supaya tampilannya konsisten begitu diketik.
                                    ->getSearchResultsUsing(function (string $search) {
                                        return BankKampus::query()
                                            ->where('is_active', true)
                                            ->where('nama_bank', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn($bank) => [
                                                $bank->id => view('components.bank-option-label', ['bank' => $bank])->render(),
                                            ]);
                                    })
                                    // FIX: label untuk value yang sudah terpilih di-resolve satu per satu,
                                    // bukan me-render ulang seluruh daftar bank di setiap render form.
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $bank = BankKampus::find($value);

                                        return $bank
                                            ? view('components.bank-option-label', ['bank' => $bank])->render()
                                            : null;
                                    })
                                    ->helperText('Pilih bank tujuan tempat Anda mentransfer uang pembayaran.'),

                                DateTimePicker::make('waktu_transfer')
                                    ->label('Tanggal & Waktu di Struk Transfer')
                                    ->default(now())
                                    ->required()
                                    // FIX: struk transfer tidak mungkin bertanggal di masa depan.
                                    ->maxDate(now())
                                    ->helperText('Sesuaikan dengan tanggal dan jam yang tertera pada bukti transfer Anda.'),

                                FileUpload::make('file_bukti')
                                    ->label('Upload Foto/Scan Bukti Transfer')
                                    ->image()
                                    // FIX: helper text mengklaim "JPG/PNG" — validasi sekarang menegakkannya.
                                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                    ->imageEditor()
                                    ->disk('public')
                                    ->maxSize(2048)
                                    ->directory('bukti-pembayaran')
                                    ->helperText('Pastikan Nominal, Tanggal, dan Rekening Tujuan terbaca jelas agar cepat di-ACC. Maksimal 2MB (JPG/PNG).')
                                    ->required(),

                                Textarea::make('catatan')
                                    ->label('Catatan Tambahan (Opsional)')
                                    ->placeholder('Misal: Pembayaran cicilan SPP ke-2 dari Rekening BCA an. Budi')
                                    ->rows(2),
                            ]),

                        Step::make('Konfirmasi')
                            ->description('Periksa kembali sebelum kirim')
                            ->schema([
                                TextEntry::make('summary')
                                    ->label('')
                                    ->state(function (Get $get, $record) {
                                        $sisaTagihan = $record->total_tagihan - $record->total_bayar;
                                        $nominalBayar = (int) $get('nominal_bayar');
                                        $sisaSetelahBayar = $sisaTagihan - $nominalBayar;

                                        $nominalTerbilang = Terbilang::make($nominalBayar);

                                        $bankId = $get('bank_tujuan_id');
                                        $bank = $bankId ? BankKampus::find($bankId) : null;

                                        $namaBankLengkap = $bank
                                            ? e($bank->nama_bank) . ' - ' . e($bank->no_rekening) . ' (a.n. ' . e($bank->atas_nama) . ')'
                                            : '<span class="text-danger-500">Bank belum dipilih</span>';

                                        $waktuTransfer = $get('waktu_transfer')
                                            ? Carbon::parse($get('waktu_transfer'))->translatedFormat('d F Y, H:i')
                                            : '-';

                                        $catatan = e($get('catatan') ?: 'Tidak ada catatan');

                                        $html = "<div class='space-y-4'>";

                                        // --- KOTAK 1: RINCIAN NOMINAL ---
                                        $html .= "<div class='text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700'>";
                                        $html .= "<div class='text-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-3'>";
                                        $html .= "<span class='block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1'>Nominal Yang Akan Dilaporkan</span>";

                                        $html .= "<strong class='text-3xl text-primary-600 dark:text-primary-400'>Rp " . number_format($nominalBayar, 0, ',', '.') . "</strong>";

                                        $html .= "<div class='text-sm text-gray-600 dark:text-gray-400 font-medium italic mt-1 pb-1'>({$nominalTerbilang})</div>";
                                        $html .= "</div>";

                                        $html .= "<div class='flex justify-between items-center mb-2'><span>Sisa Tagihan Saat Ini:</span> <strong>Rp " . number_format($sisaTagihan, 0, ',', '.') . "</strong></div>";

                                        if ($sisaSetelahBayar < 0) {
                                            $kelebihan = abs($sisaSetelahBayar);
                                            $html .= "<div class='p-3 bg-warning-50 dark:bg-warning-500/10 border border-warning-200 dark:border-warning-500 rounded-lg mt-3'>";
                                            $html .= "<strong class='text-warning-700 dark:text-warning-400 flex items-center gap-2'>⚠️ Peringatan: Anda Membayar Lebih</strong>";
                                            $html .= "<p class='text-warning-600 dark:text-warning-500 text-xs mt-1 leading-relaxed'>Nominal ini melebihi tagihan. Kelebihan <strong>Rp " . number_format($kelebihan, 0, ',', '.') . "</strong> akan masuk ke saldo/kredit Anda.</p>";
                                            $html .= "</div>";
                                        } else {
                                            $html .= "<div class='flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-2 mt-2'><span>Sisa Tagihan Nanti:</span> <strong class='text-success-600 dark:text-success-400'>Rp " . number_format($sisaSetelahBayar, 0, ',', '.') . "</strong></div>";
                                        }
                                        $html .= "</div>";

                                        // --- KOTAK 2: DETAIL TUJUAN ---
                                        $html .= "<div class='text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700'>";
                                        $html .= "<h4 class='font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2 mb-3'>Detail Tujuan Transfer</h4>";
                                        $html .= "<div class='space-y-3'>";

                                        $html .= "<div class='flex flex-col sm:flex-row sm:justify-between gap-1'>";
                                        $html .= "<span class='text-gray-500 text-xs sm:text-sm'>Dikirim ke Rekening:</span>";
                                        $html .= "<strong class='text-right'>{$namaBankLengkap}</strong>";
                                        $html .= "</div>";

                                        $html .= "<div class='flex flex-col sm:flex-row sm:justify-between gap-1'>";
                                        $html .= "<span class='text-gray-500 text-xs sm:text-sm'>Waktu Transaksi:</span>";
                                        $html .= "<strong class='text-right'>{$waktuTransfer} WIB</strong>";
                                        $html .= "</div>";

                                        $html .= "<div class='flex flex-col sm:flex-row sm:justify-between gap-1'>";
                                        $html .= "<span class='text-gray-500 text-xs sm:text-sm'>Catatan Anda:</span>";
                                        $html .= "<strong class='text-right italic text-gray-600 dark:text-gray-400'>\"{$catatan}\"</strong>";
                                        $html .= "</div>";

                                        $html .= "</div>";
                                        $html .= "</div>";

                                        $html .= "<div class='text-center text-xs text-gray-500 dark:text-gray-400 mt-2'>";
                                        $html .= "Pastikan nominal, tanggal, dan bank tujuan <strong>sama persis</strong> dengan foto bukti transfer yang Anda unggah.";
                                        $html .= "</div>";
                                        $html .= "</div>";

                                        return new HtmlString($html);
                                    })
                            ]),
                    ])
                    ->action(function (array $data, TagihanMahasiswa $record, Action $action) {
                        $payload = [
                            'tagihan_id'       => $record->id,
                            'tagihan_type'     => 'tagihan_mahasiswa',
                            'nominal_bayar'    => (int) $data['nominal_bayar'],
                            'tanggal_bayar'    => $data['waktu_transfer'],
                            'bukti_bayar_path' => $data['file_bukti'],
                            'bank_tujuan_id'   => $data['bank_tujuan_id'],
                            'catatan'          => $data['catatan'] ?? null,
                        ];

                        // FIX: kegagalan proses (mis. validasi service layer) sebelumnya tidak
                        // ditangani sama sekali — mahasiswa hanya melihat error generik dan
                        // kehilangan seluruh isian wizard-nya.
                        try {
                            app(MahasiswaUploadChannel::class)->process($payload);
                        } catch (\Throwable $e) {
                            report($e);

                            Notification::make()
                                ->danger()
                                ->title('Gagal Mengirim Bukti Pembayaran')
                                ->body('Terjadi kesalahan saat memproses pembayaran Anda. Silakan coba lagi beberapa saat lagi atau hubungi Staf Keuangan jika masalah berlanjut.')
                                ->send();

                            $action->halt();
                        }

                        Notification::make()
                            ->success()
                            ->title('Bukti Berhasil Terkirim! 🎉')
                            ->body('Terima kasih. Staf Keuangan akan segera memverifikasi pembayaran Anda.')
                            ->send();
                    })
            ]);
    }
}
