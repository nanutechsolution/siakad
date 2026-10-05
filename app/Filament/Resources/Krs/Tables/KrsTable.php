<?php

namespace App\Filament\Resources\Krs\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Enums\KrsStatusEnum;
use App\Enums\Pdf\PdfDocumentType;
use App\Filament\Actions\Pdf\PdfDownloadAction;
use App\Filament\Actions\Pdf\PdfPreviewAction;
use App\Filament\Support\HasKrsReviewAction;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Services\Akademik\KrsApprovalService;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KrsTable
{
    use HasKrsReviewAction;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with([
                'mahasiswa.person',
                'mahasiswa.prodi',
                'tahunAkademik',
            ]))
            // Antrean terlama dulunya — supaya SLA persetujuan terlihat.
            ->defaultSort('diajukan_at', 'asc')
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Krs $record) => $record->mahasiswa?->prodi?->nama_prodi)
                    ->wrap(),

                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Periode')
                    ->sortable(),

                TextColumn::make('total_sks_diambil')
                    ->label('SKS')
                    ->numeric()
                    ->badge()
                    ->alignCenter(),

                IconColumn::make('status_keuangan')
                    ->label('Keuangan')
                    ->boolean()
                    ->getStateUsing(fn(Krs $record): bool => self::lolosKeuangan($record))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn(Krs $record): string => self::lolosKeuangan($record)
                        ? 'Lolos PaymentPolicy'
                        : 'Belum lolos PaymentPolicy'),

                TextColumn::make('status_krs')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?KrsStatusEnum $state) => $state?->getLabel() ?? '-')
                    ->color(fn(?KrsStatusEnum $state) => $state?->getColor() ?? 'gray'),

                TextColumn::make('diajukan_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Belum diajukan'),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun'),

                SelectFilter::make('status_krs')
                    ->label('Status KRS')
                    ->options(KrsStatusEnum::options()),

                // Filter bernama `prodi_id` (bukan `mahasiswa.prodi_id`) karena
                // kolom tidak ada di tabel `krs` — query dibuat manual via
                // whereHas, bukan nama kolom langsung seperti nama relasi.
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(Auth::user()))
                    ->searchable()
                    ->preload()
                    ->query(fn(Builder $query, array $data): Builder => blank($data['value'] ?? null)
                        ? $query
                        : $query->whereHas(
                            'mahasiswa',
                            fn(Builder $q) => $q->where('prodi_id', (int) $data['value']),
                        )),
            ])
            ->recordActions([
                ActionGroup::make([
                    static::makeKrsReviewAction('admin'),
                    ViewAction::make(),

                    EditAction::make()
                        ->visible(fn(Krs $record) => in_array($record->status_krs, [
                            KrsStatusEnum::DRAFT,
                            KrsStatusEnum::DITOLAK,
                        ], true))
                        ->authorize('update'),

                    Action::make('ajukan')
                        ->label(fn(Krs $record): string => $record->status_krs === KrsStatusEnum::DITOLAK
                            ? 'Ajukan Kembali'
                            : 'Ajukan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn(Krs $record) => in_array($record->status_krs, [
                            KrsStatusEnum::DRAFT,
                            KrsStatusEnum::DITOLAK,
                        ], true))
                        ->authorize('update')
                        ->requiresConfirmation()
                        ->action(fn(Krs $record) => self::ajukan($record)),

                    Action::make('buka_kembali')
                        ->label('Buka Kembali KRS')
                        ->icon('heroicon-o-lock-open')
                        ->color('warning')
                        ->visible(fn(Krs $record) => $record->status_krs === KrsStatusEnum::DISETUJUI)
                        ->authorize('cancel')
                        ->schema([
                            Textarea::make('catatan_admin')
                                ->label('Alasan Membuka Kembali KRS')
                                ->required(),
                        ])
                        ->action(fn(array $data, Krs $record) => self::bukaKembali($record, $data['catatan_admin'])),

                    Action::make('batalkan')
                        ->label('Batalkan KRS')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->visible(fn(Krs $record) => $record->status_krs === KrsStatusEnum::DISETUJUI)
                        ->authorize('cancel')
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('catatan_admin')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(fn(array $data, Krs $record) => self::cancel($record, $data['catatan_admin'])),

                    Action::make('override_keuangan')
                        ->label('Override Keuangan')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->visible(fn(Krs $record) => ! self::lolosKeuangan($record))
                        ->authorize('update')
                        ->schema([
                            Textarea::make('financial_override_reason')
                                ->label('Alasan Override/Dispensasi Pembayaran')
                                ->required(),
                        ])
                        ->action(fn(array $data, Krs $record) => self::overrideFinance(
                            $record,
                            $data['financial_override_reason'],
                        )),

                    PdfPreviewAction::makeArchived(
                        name: 'cetak',
                        label: 'Pratinjau & Cetak KRS',
                        type: PdfDocumentType::KRS,
                        contextResolver: fn(Krs $record) => ['krs_id' => $record->id],
                        documentableResolver: fn(Krs $record) => [
                            'documentableType' => \App\Models\Mahasiswa::class,
                            'documentableId' => $record->mahasiswa_id,
                        ],
                    ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription(
                            'Setiap KRS diperiksa ulang per baris: harus berstatus Diajukan, lolos verifikasi keuangan, dan sesuai kewenangan Anda. Yang tidak memenuhi akan dilewati.',
                        )
                        ->action(function (Collection $records, KrsApprovalService $service): void {
                            $berhasil = 0;
                            $dilewati = 0;
                            $gagal = 0;
                            $user = Auth::user();

                            foreach ($records as $record) {
                                $layak = $user
                                    && $user->can('approve', $record)
                                    && $record->status_krs === KrsStatusEnum::DIAJUKAN
                                    && self::lolosKeuangan($record);

                                if (! $layak) {
                                    $dilewati++;
                                    continue;
                                }

                                try {
                                    $service->approve($record);
                                    $berhasil++;
                                } catch (\Throwable) {
                                    $gagal++;
                                }
                            }

                            Notification::make()
                                ->title('Proses persetujuan selesai')
                                ->body("{$berhasil} berhasil, {$dilewati} dilewati, {$gagal} gagal karena validasi atau kuota kelas.")
                                ->color($gagal > 0 || $dilewati > 0 ? 'warning' : 'success')
                                ->persistent()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /** @var array<string, bool> cache per-request supaya PaymentPolicy tidak dihitung ulang per render */
    protected static array $cacheKeuangan = [];

    /**
     * Sumber kebenaruan status keuangan antar UI:
     * flag override/manual ATAU perhitungan live PaymentPolicy.
     */
    protected static function lolosKeuangan(Krs $record): bool
    {
        if ($record->is_financial_verified) {
            return true;
        }

        $key = $record->getKey();

        if (array_key_exists($key, self::$cacheKeuangan)) {
            return self::$cacheKeuangan[$key];
        }

        $mahasiswa = $record->mahasiswa;
        $ta = $record->tahunAkademik;

        if (! $mahasiswa || ! $ta) {
            return self::$cacheKeuangan[$key] = false;
        }

        return self::$cacheKeuangan[$key] = app(KrsValidationService::class)
            ->checkKeuangan($mahasiswa, $ta)
            ->passed;
    }

    /**
     * Ajukan KRS berstatus DRAFT atau DITOLAK (pengajuan kembali setelah revisi).
     *
     * Status dibaca ulang dari database dengan lock di dalam transaksi, sehingga
     * before_data pada audit log selalu status aktual (bukan hardcode DRAFT) dan
     * klik ganda / UI usang tidak menghasilkan log ganda.
     */
    protected static function ajukan(Krs $record): void
    {
        $hasil = DB::transaction(function () use ($record): array {
            $locked = Krs::withoutGlobalScopes()
                ->whereKey($record->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return ['ok' => false, 'pesan' => 'KRS tidak ditemukan.'];
            }

            $statusSebelumnya = $locked->status_krs;

            if (! in_array($statusSebelumnya, [KrsStatusEnum::DRAFT, KrsStatusEnum::DITOLAK], true)) {
                return [
                    'ok' => false,
                    'pesan' => 'KRS berstatus ' . ($statusSebelumnya?->getLabel() ?? '-') . ' dan tidak dapat diajukan.',
                ];
            }

            if (! $locked->details()->exists()) {
                return [
                    'ok' => false,
                    'pesan' => 'KRS belum memiliki mata kuliah. Lengkapi mata kuliah terlebih dahulu sebelum diajukan.',
                ];
            }

            $revisi = $statusSebelumnya === KrsStatusEnum::DITOLAK;

            $atribut = [
                'status_krs' => KrsStatusEnum::DIAJUKAN,
                'diajukan_at' => now(),
            ];

            if ($revisi) {
                // Riwayat penolakan tetap tersimpan di krs_status_logs; kolom
                // ringkasan di-reset agar tidak terbaca sebagai hasil review baru.
                $atribut['ditolak_oleh'] = null;
                $atribut['ditolak_pada'] = null;
                $atribut['catatan_admin'] = null;
            }

            $locked->forceFill($atribut)->save();

            DB::table('krs_status_logs')->insert([
                'krs_id' => $locked->getKey(),
                'aksi' => 'DIAJUKAN',
                'dilakukan_oleh' => Auth::id(),
                'before_data' => json_encode(['status_krs' => $statusSebelumnya->value]),
                'after_data' => json_encode(['status_krs' => KrsStatusEnum::DIAJUKAN->value]),
                'catatan' => $revisi
                    ? 'KRS diajukan kembali setelah revisi.'
                    : 'KRS diajukan untuk persetujuan.',
                'created_at' => now(),
            ]);

            return ['ok' => true, 'revisi' => $revisi];
        });

        if (! $hasil['ok']) {
            Notification::make()
                ->title('KRS tidak dapat diajukan')
                ->body($hasil['pesan'])
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title($hasil['revisi'] ? 'KRS diajukan kembali untuk persetujuan' : 'KRS diajukan untuk persetujuan')
            ->success()
            ->send();
    }

    /**
     * Buka kembali KRS yang sudah DISETUJUI (kembali ke DRAFT).
     *
     * KRS DISETUJUI sudah menambah isi_kelas saat approve, sehingga membuka
     * kembali WAJIB mengembalikannya (simetris dengan cancel). Tanpa ini,
     * approve berikutnya menambah isi_kelas untuk kedua kalinya.
     */
    protected static function bukaKembali(Krs $record, string $catatan): void
    {
        $pesanGagal = DB::transaction(function () use ($record, $catatan): ?string {
            $locked = Krs::withoutGlobalScopes()
                ->whereKey($record->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->status_krs !== KrsStatusEnum::DISETUJUI) {
                return 'Hanya KRS berstatus Disetujui yang dapat dibuka kembali.';
            }

            self::kembalikanKapasitas($locked);

            $locked->update([
                'status_krs' => KrsStatusEnum::DRAFT,
                'catatan_admin' => $catatan,
            ]);

            DB::table('krs_status_logs')->insert([
                'krs_id' => $locked->getKey(),
                'aksi' => 'DIBUKA_KEMBALI',
                'dilakukan_oleh' => Auth::id(),
                'before_data' => json_encode(['status_krs' => KrsStatusEnum::DISETUJUI->value]),
                'after_data' => json_encode(['status_krs' => KrsStatusEnum::DRAFT->value]),
                'catatan' => $catatan,
                'created_at' => now(),
            ]);

            return null;
        });

        if ($pesanGagal !== null) {
            Notification::make()
                ->title('KRS tidak dapat dibuka kembali')
                ->body($pesanGagal)
                ->warning()
                ->send();

            return;
        }

        Notification::make()->title('KRS dibuka kembali')->body('Status kembali ke Draft untuk direvisi.')->success()->send();
    }

    protected static function cancel(Krs $record, string $catatan): void
    {
        $pesanGagal = DB::transaction(function () use ($record, $catatan): ?string {
            $locked = Krs::withoutGlobalScopes()
                ->whereKey($record->getKey())
                ->lockForUpdate()
                ->first();

            // Status dicek di dalam lock: mencegah isi_kelas dikurangi dua kali
            // bila tombol ditekan ulang dari tampilan yang sudah usang.
            if (! $locked || $locked->status_krs !== KrsStatusEnum::DISETUJUI) {
                return 'Hanya KRS berstatus Disetujui yang dapat dibatalkan.';
            }

            $locked->update([
                'status_krs' => KrsStatusEnum::DIBATALKAN,
                'catatan_admin' => $catatan,
            ]);

            // KRS yang disetujui sudah menambah isi_kelas saat approve
            // (observer tidak aktif), jadi pembatalan harus mengembalikannya.
            self::kembalikanKapasitas($locked);

            DB::table('krs_status_logs')->insert([
                'krs_id' => $locked->getKey(),
                'aksi' => 'DIBATALKAN',
                'dilakukan_oleh' => Auth::id(),
                'before_data' => json_encode(['status_krs' => KrsStatusEnum::DISETUJUI->value]),
                'after_data' => json_encode(['status_krs' => KrsStatusEnum::DIBATALKAN->value]),
                'catatan' => $catatan,
                'created_at' => now(),
            ]);

            return null;
        });

        if ($pesanGagal !== null) {
            Notification::make()
                ->title('KRS tidak dapat dibatalkan')
                ->body($pesanGagal)
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('KRS dibatalkan')
            ->body('KRS mahasiswa tidak lagi berlaku pada periode ini.')
            ->success()
            ->send();
    }

    /**
     * Kembalikan isi_kelas untuk seluruh jadwal pada KRS yang pernah DISETUJUI.
     * Himpunan jadwal sama persis dengan yang ditambah KrsApprovalService::approve().
     * Wajib dipanggil di dalam DB::transaction().
     */
    protected static function kembalikanKapasitas(Krs $krs): void
    {
        $jadwalIds = $krs->details()->pluck('jadwal_kuliah_id')->filter()->values()->all();

        if ($jadwalIds === []) {
            return;
        }

        // Kunci baris jadwal terlebih dahulu (urutan lock sama dengan approve).
        JadwalKuliah::query()
            ->whereIn('id', $jadwalIds)
            ->lockForUpdate()
            ->get(['id']);

        JadwalKuliah::query()
            ->whereIn('id', $jadwalIds)
            ->where('isi_kelas', '>', 0)
            ->decrement('isi_kelas');
    }

    protected static function overrideFinance(Krs $record, string $reason): void
    {
        $record->update([
            'is_financial_verified' => true,
            'financial_override_by' => Auth::id(),
            'financial_override_reason' => $reason,
        ]);

        Notification::make()
            ->title('Verifikasi keuangan dioverride')
            ->body('KRS kini dapat diproses persetujuannya.')
            ->success()
            ->send();
    }
}
