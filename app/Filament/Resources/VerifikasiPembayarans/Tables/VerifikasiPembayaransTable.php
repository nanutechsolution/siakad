<?php

namespace App\Filament\Resources\VerifikasiPembayarans\Tables;

use App\Enums\StatusVerifikasiPembayaran;
use App\Models\PembayaranMahasiswa;
use App\Services\Pembayaran\PembayaranVerificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

                ImageColumn::make('bukti_bayar_path')
                    ->label('Bukti')
                    ->square()
                    ->visibility('private') // Sesuaikan jika Anda pakai disk 'private'
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->action(
                        // Membuka modal preview gambar secara penuh
                        Action::make('lihat_bukti')
                            ->modalHeading('Bukti Pembayaran (Private Secure)')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->modalContent(function ($record) {
                                // 1. Tentukan nama disk private Anda. 
                                // Jika Anda pakai bawaan Laravel (storage/app), gunakan 'local'.
                                // Jika Anda membuat disk baru di filesystems.php bernama 'private', ubah menjadi 'private'.
                                $disk = 'local';

                                // 2. Cek apakah file benar-benar ada di folder private
                                if (!Storage::disk($disk)->exists($record->bukti_bayar_path)) {
                                    return new HtmlString('
                        <div class="text-center p-6 text-danger-600 dark:text-danger-400 font-medium bg-danger-50 dark:bg-danger-950 rounded-lg">
                            ⚠️ File bukti transfer tidak ditemukan di dalam private storage.
                        </div>
                    ');
                                }

                                try {
                                    // 3. Ambil data mentah file secara internal di server
                                    $fileContent = Storage::disk($disk)->get($record->bukti_bayar_path);
                                    $mimeType = Storage::disk($disk)->mimeType($record->bukti_bayar_path);

                                    // 4. Encode menjadi Base64 agar bisa dirender langsung oleh tag <img> tanpa URL
                                    $base64 = base64_encode($fileContent);
                                    $base64Url = "data:{$mimeType};base64,{$base64}";

                                    return new HtmlString('
                        <div class="flex justify-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
                            <img src="' . $base64Url . '" alt="Bukti Transfer" class="max-w-full h-auto max-h-[70vh] rounded-lg shadow-md object-contain" />
                        </div>
                    ');
                                } catch (\Exception $e) {
                                    return new HtmlString('
                        <div class="text-center p-4 text-danger-600 font-medium">
                            Gagal memuat gambar: ' . $e->getMessage() . '
                        </div>
                    ');
                                }
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
                                    $record->id,
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
                        ->form([
                            Textarea::make('alasan_penolakan')
                                ->label('Alasan Penolakan')
                                ->required() // Wajib diisi
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
