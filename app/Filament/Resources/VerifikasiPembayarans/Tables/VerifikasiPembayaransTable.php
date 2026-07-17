<?php

namespace App\Filament\Resources\VerifikasiPembayarans\Tables;

use App\Enums\StatusVerifikasiPembayaran;
use App\Models\PembayaranMahasiswa;
use App\Services\Pembayaran\PembayaranVerificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['tagihan.mahasiswa']))
            ->columns([
                TextColumn::make('tagihan.mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->tagihan?->mahasiswa?->nim)
                    ->weight('bold'),

                TextColumn::make('tagihan.kode_transaksi')
                    ->label('No. Tagihan')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('nominal_bayar')
                    ->label('Nominal Bayar')
                    ->money('IDR')
                    ->alignment('right')
                    ->weight('bold')
                    ->color('success'),

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
                        Action::make('lihat_bukti')
                            ->modalHeading('Bukti Pembayaran')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->modalContent(function ($record) {
                                $disk = 'public';
                                if (!Storage::disk($disk)->exists($record->bukti_bayar_path)) {
                                    return new HtmlString('
                        <div class="text-center p-6 text-danger-600 dark:text-danger-400 font-medium">
                            ⚠️ File bukti pembayaran tidak ditemukan.
                        </div>
                    ');
                                }
                                $mimeType = Storage::disk($disk)
                                    ->mimeType($record->bukti_bayar_path);

                                $fileUrl = Storage::disk($disk)
                                    ->url($record->bukti_bayar_path);

                                // Preview gambar
                                if (str_starts_with($mimeType, 'image/')) {
                                    return new HtmlString('
                        <div class="flex justify-center p-4">
                            <img 
                                src="' . $fileUrl . '" 
                                alt="Bukti Pembayaran"
                                class="max-w-full max-h-[70vh] rounded-lg shadow"
                            />
                        </div>
                    ');
                                }

                                // Preview PDF
                                if ($mimeType === 'application/pdf') {
                                    return new HtmlString('
                        <div class="p-4">
                            <iframe
                                src="' . $fileUrl . '"
                                class="w-full h-[70vh] rounded-lg border"
                            ></iframe>
                        </div>
                    ');
                                }

                                return new HtmlString('
                    <div class="text-center p-6">
                        File tidak dapat dipreview.
                        <br>
                        <a 
                            href="' . $fileUrl . '" 
                            target="_blank"
                            class="text-primary-600 underline"
                        >
                            Download File
                        </a>
                    </div>
                ');
                            })
                    ),
                TextColumn::make('status_verifikasi_id')
                    ->label('Status')
                    ->badge()
                    // Karena sudah di-cast di Model, $state adalah instance dari Enum!
                    ->formatStateUsing(fn(StatusVerifikasiPembayaran $state): string => $state->label())
                    ->color(fn(StatusVerifikasiPembayaran $state): string => $state->badgeColor()),
                TextColumn::make('status_verifikasi_id')
                    ->label('Status')
                    ->badge()
                    // Karena sudah di-cast di Model, $state adalah instance dari Enum!
                    ->formatStateUsing(fn(StatusVerifikasiPembayaran $state): string => $state->label())
                    ->color(fn(StatusVerifikasiPembayaran $state): string => $state->badgeColor()),
            ])
            ->filters([
                SelectFilter::make('status_verifikasi_id')
                    ->label('Filter Status')
                    ->options(StatusVerifikasiPembayaran::class)
                    ->default(StatusVerifikasiPembayaran::PENDING->value),
            ])

            ->recordActions([
                ActionGroup::make([
                    // 1. ACTION: TERIMA (VERIFIKASI)
                    Action::make('approve')
                        ->label('Terima Pembayaran')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn($record) => $record->status_verifikasi_id === StatusVerifikasiPembayaran::PENDING)
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Pembayaran?')
                        ->modalDescription('Tindakan ini akan mengesahkan pembayaran, mendistribusikan alokasi biaya, dan memperbarui saldo mahasiswa jika ada sisa bayar. Tindakan ini tidak bisa diurungkan.')
                        // Hapus ->form() catatan_admin karena method verifikasi() di Service tidak menerimanya
                        ->action(function (PembayaranMahasiswa $record) {
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
                        ->visible(fn($record) => $record->status_verifikasi_id === StatusVerifikasiPembayaran::PENDING)
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
                ]),
            ])
            ->toolbarActions([]);
    }
}
