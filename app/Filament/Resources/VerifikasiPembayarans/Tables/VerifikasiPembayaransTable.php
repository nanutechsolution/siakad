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
use Filament\Tables\Columns\Summarizers\Sum;
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
            ->striped() // UI: Memudahkan membaca baris
            ->deferLoading() // UX: Loading skeleton saat data banyak
            ->modifyQueryUsing(
                fn(Builder $query) => $query->with([
                    'tagihan.mahasiswa.angkatan',
                    'tagihan.mahasiswa.prodi',
                    'tagihan.mahasiswa.person',
                    'verifiedBy.person',
                ])
            )
            ->columns([
                TextColumn::make('tagihan.mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->copyable()
                    ->copyMessage('Nama disalin')
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

                TextColumn::make('tagihan.mahasiswa.prodi.nama_prodi')
                    ->label('Prodi & Angkatan')
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('tagihan.mahasiswa.prodi', function ($q) use ($search) {
                                $q->where('nama_prodi', 'like', "%{$search}%");
                            });
                        }
                    )
                    ->sortable()
                    ->description(fn($record) => 'Angkatan: ' . ($record->tagihan?->mahasiswa?->angkatan?->id_tahun ?? '-'))
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('bukti_bayar_path')
                    ->label('Bukti')
                    ->formatStateUsing(fn() => 'Lihat Bukti')
                    ->badge()
                    ->color('info')
                    ->url(function ($record) {
                        $disk = 'public';

                        if (!Storage::disk($disk)->exists($record->bukti_bayar_path)) {
                            return null;
                        }

                        return Storage::disk($disk)->url($record->bukti_bayar_path);
                    })
                    ->openUrlInNewTab(),

                TextColumn::make('status_verifikasi_id')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(StatusVerifikasiPembayaran $state): string => $state->label())
                    ->color(fn(StatusVerifikasiPembayaran $state): string => $state->badgeColor()),
                TextColumn::make('verifiedBy.person.nama_lengkap')
                    ->label('Verifikator')
                    ->placeholder('Belum diverifikasi')
                    ->description(function (PembayaranMahasiswa $record): ?string {
                        return $record->verified_at
                            ? $record->verified_at->format('d M Y, H:i')
                            : 'Menunggu verifikasi';
                    })
                    ->icon(function (PembayaranMahasiswa $record): string {
                        return $record->verified_at
                            ? 'heroicon-o-check-badge'
                            : 'heroicon-o-clock';
                    })
                    ->iconColor(function (PembayaranMahasiswa $record): string {
                        return $record->verified_at
                            ? 'success'
                            : 'warning';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('tanggal_bayar')
                    ->label('Tgl Transfer & Input')
                    ->dateTime('d M Y, H:i') // Menampilkan tanggal transfer sebagai teks utama
                    ->sortable()
                    // Menambahkan tanggal upload/input di bawahnya sebagai deskripsi
                    ->description(fn($record) => $record->created_at ? 'Diinput: ' . $record->created_at->format('d M Y, H:i') : '-')
                    // UX: Memberi warna kuning (warning) jika mahasiswa telat upload bukti > 1 hari dari tanggal transfer
                    ->color(function ($record) {
                        if (!$record->tanggal_bayar || !$record->created_at) {
                            return null;
                        }

                        // Pastikan formatnya Carbon untuk menghitung selisih hari
                        $tglBayar = \Carbon\Carbon::parse($record->tanggal_bayar);

                        if ($record->created_at->diffInDays($tglBayar) > 1) {
                            return 'warning';
                        }

                        return null; // Warna default (hitam/putih) jika jarak harinya wajar
                    }),
            ])
            ->defaultSort('created_at', 'asc')
            ->filters([
                SelectFilter::make('status_verifikasi_id')
                    ->label('Status')
                    ->options([
                        StatusVerifikasiPembayaran::PENDING->value =>
                        StatusVerifikasiPembayaran::PENDING->label(),

                        StatusVerifikasiPembayaran::VERIFIED->value =>
                        StatusVerifikasiPembayaran::VERIFIED->label(),

                        StatusVerifikasiPembayaran::REJECTED->value =>
                        StatusVerifikasiPembayaran::REJECTED->label(),
                    ])
                    ->default(
                        StatusVerifikasiPembayaran::PENDING->value
                    ),

                // UX: Filter Prodi. Jika nested (level 3) sering error, gunakan form query builder seperti ini
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->options(
                        fn() =>
                        RefProdi::query()
                            ->orderBy('nama_prodi')
                            ->pluck('nama_prodi', 'id')
                            ->toArray()
                    )
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            if (blank($data['value'] ?? null)) {
                                return $query;
                            }

                            return $query->whereHas(
                                'tagihan.mahasiswa',
                                fn(Builder $q) =>
                                $q->where(
                                    'prodi_id',
                                    $data['value']
                                )
                            );
                        }
                    ),

                SelectFilter::make('angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->options(
                        fn() =>
                        RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck(
                                'id_tahun',
                                'id_tahun'
                            )
                            ->toArray()
                    )
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            if (blank($data['value'] ?? null)) {
                                return $query;
                            }

                            return $query->whereHas(
                                'tagihan.mahasiswa.angkatan',
                                fn($q) =>
                                $q->where(
                                    'id_tahun',
                                    $data['value']
                                )
                            );
                        }
                    ),
            ])
            ->recordActions([
                // 1. ACTION: TERIMA (VERIFIKASI)
                Action::make('approve')
                    ->label('Terima')
                    ->icon('heroicon-m-check-circle')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->visible(
                        fn($record) =>
                        $record->status_verifikasi_id === StatusVerifikasiPembayaran::PENDING
                            && auth()->user()->can('ApprovePembayaran')
                    )
                    ->requiresConfirmation()
                    ->modalHeading(fn(PembayaranMahasiswa $record) => 'Setujui Pembayaran ' . ($record->tagihan?->mahasiswa?->person?->nama_lengkap ?? 'Mahasiswa') . '?')

                    ->modalDescription(fn(PembayaranMahasiswa $record) => new HtmlString(
                        'Anda akan menerima pembayaran sebesar <strong>Rp ' . number_format($record->nominal_bayar, 0, ',', '.') . '</strong>.<br><br>Tindakan ini akan mengesahkan pembayaran, mendistribusikan alokasi biaya, dan memperbarui saldo mahasiswa jika ada sisa bayar. Tindakan ini tidak bisa diurungkan.'
                    ))
                    ->action(function (PembayaranMahasiswa $record) {
                        abort_unless(auth()->user()->can('ApprovePembayaran'), 403);
                        try {
                            app(PembayaranVerificationService::class)->verifikasi(
                                $record,
                                auth()->id()
                            );

                            Notification::make()
                                ->title('Pembayaran Disetujui')
                                ->body('Dana pembayaran atas nama ' . ($record->tagihan?->mahasiswa?->person?->nama_lengkap ?? 'mahasiswa') . ' telah dialokasikan.')
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
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
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
            ])
            ->toolbarActions([]);
    }
}
