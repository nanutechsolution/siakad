<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Pages;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\StatusVerifikasiPembayaran;
use App\Enums\MetodePembayaran;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\TagihanMahasiswaResource;
use App\Models\KeuanganSaldo;
use App\Models\KeuanganSaldoTransaction;
use App\Models\PembayaranMahasiswa;
use App\Models\TagihanMahasiswa;
use App\Services\Pdf\PdfService;
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
use Illuminate\Support\Facades\Auth;
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
                    // Kunci baris TAGIHAN dan SALDO di dalam transaksi — kalau
                    // di luar, MySQL meng-commit otomatis sehingga lock langsung
                    // lepas dan dua pembayaran paralel bisa double-spend saldo.
                    $processed = DB::transaction(function () use ($data, $allocationService) {
                        $tagihan = TagihanMahasiswa::whereKey($this->record->getKey())
                            ->lockForUpdate()
                            ->firstOrFail();

                        $saldo = KeuanganSaldo::where('mahasiswa_id', $tagihan->mahasiswa_id)
                            ->lockForUpdate()
                            ->first();

                        if ($saldo === null) {
                            return 'no-saldo';
                        }

                        $nominal = number_format((float) $data['nominal_bayar'], 2, '.', '');
                        $sisaTagihan = bcsub((string) $tagihan->total_tagihan, (string) $tagihan->total_bayar, 2);

                        // Form sudah memvalidasi, tapi saldo/tagihan bisa berubah
                        // sejak modal dibuka — cek ulang setelah baris dikunci supaya
                        // saldo deposit tidak pernah jadi negatif dan pembayaran
                        // tidak melebihi sisa tagihan.
                        if (bccomp($nominal, (string) $saldo->saldo, 2) === 1
                            || bccomp($nominal, $sisaTagihan, 2) === 1
                            || bccomp($nominal, '0.00', 2) <= 0) {
                            return 'stale';
                        }

                        // 1. Potong saldo utama mahasiswa
                        $saldo->saldo = bcsub((string) $saldo->saldo, $nominal, 2);
                        $saldo->last_updated_at = now();
                        $saldo->save();

                        // 2. Buat data pembayaran langsung DITERIMA (memakai dana
                        //    internal bank sistem). Kolom yang dipakai harus sesuai
                        //    skema: tagihan_type wajib terisi, dan tidak ada kolom
                        //    `bank_tujuan`/`catatan` pada tabel ini.
                        $pembayaran = PembayaranMahasiswa::create([
                            'id' => Str::uuid()->toString(),
                            'idempotency_key' => Str::uuid()->toString(),
                            'tagihan_id' => $tagihan->id,
                            'tagihan_type' => $tagihan->getMorphClass(),
                            'nominal_bayar' => $nominal,
                            'tanggal_bayar' => now(),
                            'metode_pembayaran' => MetodePembayaran::ADMIN,
                            'bukti_bayar_path' => null,
                            'keterangan_pengirim' => 'SALDO DEPOSIT',
                            'catatan_verifikasi' => 'Pembayaran instan via potong Saldo Deposit Mahasiswa.',
                            'status_verifikasi_id' => StatusVerifikasiPembayaran::VERIFIED,
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);

                        // 3. Catat riwayat log mutasi keluar (OUT)
                        KeuanganSaldoTransaction::create([
                            'saldo_id' => $saldo->id,
                            'tipe' => 'OUT',
                            'nominal' => $nominal,
                            'referensi_id' => $pembayaran->id,
                            'keterangan' => 'Pembayaran tagihan invoice ' . $tagihan->kode_transaksi,
                        ]);

                        // 4. Eksekusi alokasi pembagian dana FIFO ke tagihan detail komponen biaya
                        $allocationService->alokasikan($pembayaran);

                        return 'ok';
                    });

                    if ($processed !== 'ok') {
                        Notification::make()
                            ->danger()
                            ->title('Pembayaran tidak dapat diproses')
                            ->body($processed === 'no-saldo'
                                ? 'Akun Anda belum memiliki saldo deposit.'
                                : 'Saldo deposit atau sisa tagihan sudah berubah. Muat ulang halaman lalu coba lagi.')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Pembayaran Berhasil')
                        ->body('Tagihan Anda telah berhasil dibayar secara instan menggunakan Saldo Deposit.')
                        ->send();

                    return redirect()->to(TagihanMahasiswaResource::getUrl('view', ['record' => $this->record]));
                }),
            Action::make('cetak-invoice')
                ->label('Cetak Invoice')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    $document = app(PdfService::class)->generateArchived(
                        type: PdfDocumentType::INVOICE_TAGIHAN,
                        context: ['tagihan_id' => $this->record->id],
                        documentableType: TagihanMahasiswa::class,
                        documentableId: $this->record->id,
                    );

                    return app(PdfService::class)->downloadArchived($document);
                }),


        ];
    }
}
