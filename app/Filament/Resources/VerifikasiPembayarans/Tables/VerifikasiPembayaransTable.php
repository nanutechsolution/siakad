<?php

namespace App\Filament\Resources\VerifikasiPembayarans\Tables;

use App\Enums\Pdf\PdfDocumentType;
use App\Enums\StatusVerifikasiPembayaran;
use App\Models\PembayaranMahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Services\Pdf\PdfService;
use App\Services\Pembayaran\PembayaranVerificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class VerifikasiPembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped() // UI: Memudahkan membaca baris
            ->deferLoading() // UX: Loading skeleton saat data banyak
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['tagihan.mahasiswa.angkatan', 'tagihan.mahasiswa.prodi']))
            ->columns([
                TextColumn::make('tagihan.mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable(
                        // UX: Bisa mencari nama mahasiswa ATAU NIM sekaligus
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('tagihan.mahasiswa', function ($q) use ($search) {
                                $q->where('nim', 'like', "%{$search}%")
                                    ->orWhereHas('person', fn($qPerson) => $qPerson->where('nama_lengkap', 'like', "%{$search}%"));
                            });
                        }
                    )
                    ->sortable()
                    ->description(fn($record) => $record->tagihan?->mahasiswa?->nim)
                    ->weight('bold'),

                // UI/UX: Menggabungkan Prodi & Angkatan jadi 1 kolom agar hemat ruang tabel
                TextColumn::make('tagihan.mahasiswa.prodi.nama_prodi')
                    ->label('Prodi & Angkatan')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => 'Angkatan: ' . ($record->tagihan?->mahasiswa?->angkatan?->id_tahun ?? '-'))
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('nominal_bayar')
                    ->label('Nominal Bayar')
                    ->money('IDR')
                    ->alignment('right')
                    ->weight('bold')
                    ->color('success')
                    // UX: Menambahkan total uang di bawah tabel untuk admin keuangan
                    ->summarize(
                        Sum::make()->label('Total')->money('IDR')
                    ),

                TextColumn::make('tanggal_bayar')
                    ->label('Tgl Transfer')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('bukti_bayar_path')
                    ->label('Bukti')
                    ->formatStateUsing(fn() => 'Lihat Bukti')
                    ->badge()
                    ->color('info')
                    ->action(
                        // (Action modal lihat_bukti Anda tetap sama)
                        Action::make('lihat_bukti')
                            ->modalHeading('Bukti Pembayaran')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->modalContent(function ($record) {
                                $disk = 'public';
                                if (!Storage::disk($disk)->exists($record->bukti_bayar_path)) {
                                    return new HtmlString('<div class="text-center p-6 text-danger-600 font-medium">⚠️ File bukti tidak ditemukan.</div>');
                                }

                                $mimeType = Storage::disk($disk)->mimeType($record->bukti_bayar_path);
                                $fileUrl = Storage::disk($disk)->url($record->bukti_bayar_path);

                                if (str_starts_with($mimeType, 'image/')) {
                                    return new HtmlString('
                                        <div class="flex flex-col items-center gap-4 p-4">
                                            <div class="flex gap-2">
                                                <button type="button" onclick="zoomBukti(-0.25)" class="px-3 py-2 rounded bg-gray-100">−</button>
                                                <button type="button" onclick="resetZoomBukti()" class="px-3 py-2 rounded bg-gray-100">Reset</button>
                                                <button type="button" onclick="zoomBukti(0.25)" class="px-3 py-2 rounded bg-gray-100">+</button>
                                            </div>
                                            <div class="w-full overflow-auto" style="max-height: 70vh;">
                                                <div class="flex justify-center">
                                                    <img id="bukti-pembayaran-image" src="' . $fileUrl . '" class="rounded-lg shadow cursor-zoom-in" style="transition: transform 0.2s; transform-origin: center;" />
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            window.buktiZoom = 1;
                                            window.zoomBukti = function(amount) {
                                                window.buktiZoom = Math.min(Math.max(window.buktiZoom + amount, 0.5), 4);
                                                document.getElementById("bukti-pembayaran-image").style.transform = "scale(" + window.buktiZoom + ")";
                                            };
                                            window.resetZoomBukti = function() {
                                                window.buktiZoom = 1;
                                                document.getElementById("bukti-pembayaran-image").style.transform = "scale(1)";
                                            };
                                        </script>
                                    ');
                                }

                                if ($mimeType === 'application/pdf') {
                                    return new HtmlString('<div class="p-4"><iframe src="' . $fileUrl . '" class="w-full h-[70vh] rounded-lg border"></iframe></div>');
                                }

                                return new HtmlString('<div class="text-center p-6"><a href="' . $fileUrl . '" target="_blank" class="text-primary-600 underline">Download File</a></div>');
                            })
                    ),

                TextColumn::make('status_verifikasi_id')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(StatusVerifikasiPembayaran $state): string => $state->label())
                    ->color(fn(StatusVerifikasiPembayaran $state): string => $state->badgeColor()),
            ])
            ->filters([
                SelectFilter::make('status_verifikasi_id')
                    ->label('Status')
                    ->options(StatusVerifikasiPembayaran::class)
                    ->default(StatusVerifikasiPembayaran::PENDING->value),

                // UX: Filter Prodi. Jika nested (level 3) sering error, gunakan form query builder seperti ini
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->options(fn() => RefProdi::pluck('nama_prodi', 'id_prodi')->toArray()) // Sesuaikan key 'id_prodi' dengan nama PK tabel Anda
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) return $query;
                        return $query->whereHas('tagihan.mahasiswa', fn($q) => $q->where('id_prodi', $data['value']));
                    }),

                // UX: Filter Angkatan
                SelectFilter::make('angkatan')
                    ->label('Angkatan (Tahun)')
                    ->searchable()
                    ->options(fn() => RefAngkatan::pluck('id_tahun', 'id_tahun')->toArray()) // Sesuaikan dengan model Angkatan Anda
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) return $query;
                        return $query->whereHas('tagihan.mahasiswa', fn($q) => $q->where('id_tahun', $data['value']));
                    }),
            ])
            // UI/UX: Letakkan filter di atas tabel & jadikan 3 kolom sejajar
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)

            ->recordActions([
                ActionGroup::make([
                    // 1. ACTION: TERIMA (VERIFIKASI)
                    Action::make('approve')
                        ->label('Terima Pembayaran')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn($record) =>
                            $record->status_verifikasi_id === StatusVerifikasiPembayaran::PENDING
                                && auth()->user()->can('ApprovePembayaran')
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Pembayaran?')
                        ->modalDescription('Tindakan ini akan mengesahkan pembayaran, mendistribusikan alokasi biaya, dan memperbarui saldo mahasiswa jika ada sisa bayar. Tindakan ini tidak bisa diurungkan.')
                        // Hapus ->form() catatan_admin karena method verifikasi() di Service tidak menerimanya
                        ->action(function (PembayaranMahasiswa $record) {
                            abort_unless(auth()->user()->can('ApprovePembayaran'), 403);
                            try {

                                // Panggil method verifikasi() dengan meneruskan ID Pembayaran dan ID Admin yang login
                                app(PembayaranVerificationService::class)->verifikasi(
                                    $record,
                                    auth()->id() // Mengambil ID user admin yang sedang login
                                );

                                Notification::make()
                                    ->title('Pembayaran Disetujui')
                                    ->body('Dana telah dialokasikan ke tagihan mahasiswa.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal Menyetujui Pembayaran')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // 2. ACTION: TOLAK (REJECT)
                    Action::make('reject')
                        ->label('Tolak Pembayaran')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(
                            fn($record) =>
                            $record->status_verifikasi_id === StatusVerifikasiPembayaran::PENDING
                                && auth()->user()->can('TolakPembayaran')
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Bukti Pembayaran')
                        ->modalDescription('Berikan alasan yang jelas kepada mahasiswa mengapa bukti pembayaran ini ditolak (misal: gambar buram, nominal kurang).')
                        ->schema([
                            Textarea::make('alasan_penolakan')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->placeholder('Misal: Bukti transfer tidak terbaca / Nominal tidak sesuai.')
                                ->rows(3),
                        ])
                        ->action(function (PembayaranMahasiswa $record, array $data) {
                            abort_unless(auth()->user()->can('TolakPembayaran'), 403);
                            try {
                                // Panggil method tolak() dengan ID, User ID, dan Catatan
                                app(PembayaranVerificationService::class)->tolak(
                                    $record->id,
                                    auth()->id(), // ID Admin yang menolak
                                    $data['alasan_penolakan'] // Catatan dimasukkan ke sini
                                );

                                Notification::make()
                                    ->title('Pembayaran Ditolak')
                                    ->body('Status telah diubah menjadi Ditolak.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal Menolak Pembayaran')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('cetak-kwitansi')
                        ->label('Cetak Kwitansi')
                        ->icon('heroicon-o-receipt-percent')
                        ->color('success')
                        ->visible(fn($record) => $record->status_verifikasi_id === StatusVerifikasiPembayaran::VERIFIED)
                        ->action(function ($record) {
                            $document = app(PdfService::class)->generateArchived(
                                type: PdfDocumentType::KWITANSI,
                                context: ['pembayaran_id' => $record->id],
                                documentableType: PembayaranMahasiswa::class,
                                documentableId: $record->id,
                            );

                            return app(PdfService::class)->downloadArchived($document);
                        }),
                ]),
            ])
            ->toolbarActions([]);
    }
}
