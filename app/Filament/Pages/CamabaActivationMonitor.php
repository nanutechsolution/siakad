<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Mail\CicilanTerverifikasiMailable;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TagihanMahasiswa;
use App\Services\Akademik\NimService;
use App\Services\Notifications\SmsService;
use App\Services\Pembayaran\PaymentPolicyChecker;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use UnitEnum;

class CamabaActivationMonitor extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationLabel = 'Generate NIM Monitor';

    protected static ?string $title = 'Generator NIM';

    protected string $view = 'filament.pages.camaba-activation-monitor';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;

    private const STATUS_BELUM_DITAGIHKAN = 'BELUM_DITAGIHKAN';

    /** @var array<int|string, array<string, mixed>> Memo status per mahasiswa (hanya berlaku per request) */
    protected array $statusMemo = [];

    protected ?RefTahunAkademik $taMemo = null;

    protected bool $taLoaded = false;

    /** @var array<int, int|string>|null */
    protected ?array $pmbIdsMemo = null;

    /* ---------------------------------------------------------------------
     |  Helper data
     | ------------------------------------------------------------------- */

    protected function activeTa(): ?RefTahunAkademik
    {
        if (! $this->taLoaded) {
            $this->taMemo = RefTahunAkademik::where('is_active', true)->first();
            $this->taLoaded = true;
        }

        return $this->taMemo;
    }

    protected static function rupiah(float|int|string|null $nilai): string
    {
        return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
    }

    /**
     * @return array<int, int|string>
     */
    protected function pmbIds(): array
    {
        return $this->pmbIdsMemo ??= Mahasiswa::query()
            ->where('nim', 'like', 'PMB%')
            ->pluck((new Mahasiswa)->getKeyName())
            ->all();
    }

    /**
     * ID mahasiswa hasil filter + pencarian tabel yang sedang aktif.
     *
     * @return array<int, int|string>
     */
    protected function filteredIds(): array
    {
        $query = $this->getFilteredTableQuery();

        if (method_exists($this, 'applySearchToTableQuery')) {
            $query = $this->applySearchToTableQuery($query);
        }

        return $query->toBase()
            ->cloneWithout(['columns', 'orders'])
            ->cloneWithoutBindings(['select', 'order'])
            ->pluck((new Mahasiswa)->getQualifiedKeyName())
            ->all();
    }

    /**
     * Sumber tunggal status tagihan, kelayakan, dan nominal per mahasiswa.
     * Dipakai oleh kartu statistik, kolom tabel, dan filter agar selalu konsisten.
     *
     * @param  array<int, int|string>  $ids
     * @return array<int|string, array<string, mixed>>
     */
    protected function resolveStatuses(array $ids): array
    {
        $missing = array_values(array_diff($ids, array_keys($this->statusMemo)));

        if ($missing !== []) {
            $ta = $this->activeTa();
            $checker = app(PaymentPolicyChecker::class);

            foreach (array_chunk($missing, 500) as $chunk) {
                $mahasiswas = Mahasiswa::query()
                    ->with('prodi')
                    ->whereIn((new Mahasiswa)->getKeyName(), $chunk)
                    ->get()
                    ->keyBy(fn (Mahasiswa $m) => $m->getKey());

                $tagihans = TagihanMahasiswa::query()
                    ->whereIn('mahasiswa_id', $chunk)
                    ->when($ta, fn ($q) => $q->where('tahun_akademik_id', $ta->id))
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get()
                    ->groupBy('mahasiswa_id');

                foreach ($chunk as $id) {
                    $rows = $tagihans->get($id);
                    $mahasiswa = $mahasiswas->get($id);

                    if (! $rows || $rows->isEmpty() || ! $mahasiswa) {
                        $this->statusMemo[$id] = [
                            'status' => self::STATUS_BELUM_DITAGIHKAN,
                            'siap' => false,
                            'total_tagihan' => 0.0,
                            'total_bayar' => 0.0,
                            'sisa' => 0.0,
                        ];

                        continue;
                    }

                    $terbaru = $rows->first();
                    $hasil = $checker->cekKepatuhan($mahasiswa, $terbaru);

                    $this->statusMemo[$id] = [
                        'status' => strtoupper((string) $terbaru->status_bayar),
                        'siap' => (bool) ($hasil['passed'] ?? false),
                        'total_tagihan' => (float) $rows->sum('total_tagihan'),
                        'total_bayar' => (float) $rows->sum('total_bayar'),
                        'sisa' => (float) $rows->sum('sisa_tagihan'),
                    ];
                }
            }
        }

        return array_intersect_key($this->statusMemo, array_flip($ids));
    }

    /**
     * @return array<string, mixed>
     */
    protected function statusFor(Mahasiswa $record): array
    {
        $id = $record->getKey();

        return $this->resolveStatuses([$id])[$id];
    }

    /**
     * Terapkan filter berbasis status (dihitung dari basis PMB).
     */
    protected function filterByStatus(Builder $query, callable $predicate): Builder
    {
        $ids = array_keys(array_filter($this->resolveStatuses($this->pmbIds()), $predicate));

        return $query->whereIn((new Mahasiswa)->getQualifiedKeyName(), $ids);
    }

    /* ---------------------------------------------------------------------
     |  Statistik (mengikuti filter & pencarian tabel)
     | ------------------------------------------------------------------- */

    #[Computed]
    public function stats(): array
    {
        $rows = $this->resolveStatuses($this->filteredIds());

        $s = [
            'total' => count($rows),
            'total_semua' => count($this->pmbIds()),
            'siap' => 0,
            'belum_siap' => 0,
            'belum_ditagihkan' => 0,
            'belum_bayar' => 0,
            'cicilan' => 0,
            'lunas' => 0,
            'total_tagihan' => 0.0,
            'total_bayar' => 0.0,
            'tunggakan' => 0.0,
        ];

        foreach ($rows as $row) {
            $row['siap'] ? $s['siap']++ : $s['belum_siap']++;

            match ($row['status']) {
                self::STATUS_BELUM_DITAGIHKAN => $s['belum_ditagihkan']++,
                'BELUM' => $s['belum_bayar']++,
                'CICIL' => $s['cicilan']++,
                'LUNAS' => $s['lunas']++,
                default => null,
            };

            $s['total_tagihan'] += $row['total_tagihan'];
            $s['total_bayar'] += $row['total_bayar'];
            $s['tunggakan'] += $row['sisa'];
        }

        $s['progress'] = $s['total'] > 0 ? round(($s['siap'] / $s['total']) * 100, 1) : 0;
        $s['persen_terbayar'] = $s['total_tagihan'] > 0
            ? round(($s['total_bayar'] / $s['total_tagihan']) * 100, 1)
            : 0;

        return $s;
    }

    #[Computed]
    public function tahunAkademikAktif(): ?RefTahunAkademik
    {
        return $this->activeTa();
    }

    /* ---------------------------------------------------------------------
     |  Aksi NIM
     | ------------------------------------------------------------------- */

    protected function generateNim(Mahasiswa $record): void
    {
        DB::transaction(function () use ($record) {
            $prodi = RefProdi::whereKey($record->prodi_id)->lockForUpdate()->first();

            if (! $prodi) {
                throw new \RuntimeException('Prodi tidak ditemukan');
            }

            $nim = app(NimService::class)->generate($record, $prodi);

            $record->update(['nim' => $nim]);
        });

        unset($this->statusMemo[$record->getKey()]);
    }

    /* ---------------------------------------------------------------------
     |  Tabel
     | ------------------------------------------------------------------- */

    public function table(Table $table): Table
    {
        $ta = $this->activeTa();
        $batasTa = fn ($q) => $ta ? $q->where('tahun_akademik_id', $ta->id) : $q;

        return $table
            ->query(
                Mahasiswa::query()
                    ->with(['person', 'prodi', 'angkatan'])
                    ->withSum(['tagihans as total_tagihan_sum' => $batasTa], 'total_tagihan')
                    ->withSum(['tagihans as total_bayar_sum' => $batasTa], 'total_bayar')
                    ->withSum(['tagihans as total_sisa_sum' => $batasTa], 'sisa_tagihan')
            )
            ->heading('Daftar Calon Mahasiswa')
            ->description('Angka pada kartu di atas mengikuti filter dan pencarian pada tabel ini.')
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->deferFilters(false)
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->emptyStateHeading('Tidak ada data')
            ->emptyStateDescription('Tidak ada calon mahasiswa yang sesuai dengan filter atau pencarian.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->description(fn (Mahasiswa $record) => $record->person?->email)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->badge()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('status_tagihan')
                    ->label('Status Tagihan')
                    ->badge()
                    ->state(fn (Mahasiswa $record) => match ($this->statusFor($record)['status']) {
                        'BELUM' => 'Belum Bayar',
                        'CICIL' => 'Cicilan',
                        'LUNAS' => 'Lunas',
                        default => 'Belum Ditagihkan',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Belum Bayar' => 'danger',
                        'Cicilan' => 'warning',
                        'Lunas' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'Belum Bayar' => 'heroicon-m-x-circle',
                        'Cicilan' => 'heroicon-m-clock',
                        'Lunas' => 'heroicon-m-check-circle',
                        default => 'heroicon-m-minus-circle',
                    }),

                TextColumn::make('total_tagihan_sum')
                    ->label('Total Tagihan')
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_bayar_sum')
                    ->label('Terbayar')
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_sisa_sum')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->color(fn ($state) => (float) $state > 0 ? 'danger' : 'success')
                    ->weight('semibold')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('kelayakan')
                    ->label('Kelayakan NIM')
                    ->badge()
                    ->state(function (Mahasiswa $record) {
                        $status = $this->statusFor($record);

                        if ($status['status'] === self::STATUS_BELUM_DITAGIHKAN) {
                            return 'Belum Ditagihkan';
                        }

                        return $status['siap'] ? 'Siap Generate' : 'Belum Memenuhi';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Siap Generate' => 'success',
                        'Belum Memenuhi' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'Siap Generate' => 'heroicon-m-check-badge',
                        'Belum Memenuhi' => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-minus-circle',
                    }),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_nim')
                    ->label('Status NIM')
                    ->options([
                        'belum' => 'Belum Digenerate (NIM PMB)',
                        'sudah' => 'Sudah Digenerate (NIM Resmi)',
                    ])
                    ->default('belum')
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'belum' => $query->where('nim', 'like', 'PMB%'),
                            'sudah' => $query->where('nim', 'not like', 'PMB%'),
                            default => $query,
                        };
                    }),

                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status_tagihan')
                    ->label('Status Tagihan')
                    ->options([
                        'belum_ditagihkan' => 'Belum Ditagihkan',
                        'belum_bayar' => 'Belum Bayar',
                        'cicil' => 'Cicilan',
                        'lunas' => 'Lunas',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $target = match ($data['value'] ?? null) {
                            'belum_ditagihkan' => self::STATUS_BELUM_DITAGIHKAN,
                            'belum_bayar' => 'BELUM',
                            'cicil' => 'CICIL',
                            'lunas' => 'LUNAS',
                            default => null,
                        };

                        if ($target === null) {
                            return $query;
                        }

                        return $this->filterByStatus($query, fn (array $row) => $row['status'] === $target);
                    }),

                SelectFilter::make('kelayakan')
                    ->label('Kelayakan Generate NIM')
                    ->options([
                        'siap' => 'Memenuhi Persyaratan',
                        'belum' => 'Belum Memenuhi Persyaratan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'siap' => $this->filterByStatus($query, fn (array $row) => $row['siap'] === true),
                            'belum' => $this->filterByStatus($query, fn (array $row) => $row['siap'] === false),
                            default => $query,
                        };
                    }),

                Filter::make('memiliki_tunggakan')
                    ->label('Masih Memiliki Tunggakan')
                    ->toggle()
                    ->query(fn (Builder $query) => $this->filterByStatus(
                        $query,
                        fn (array $row) => $row['sisa'] > 0
                    )),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('send_reminder')
                        ->label('Kirim Reminder')
                        ->icon('heroicon-o-bell-alert')
                        ->visible(fn (Mahasiswa $record) => str_starts_with((string) $record->nim, 'PMB'))
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Reminder Pembayaran')
                        ->modalDescription('Reminder akan dikirim melalui email dan SMS sesuai data kontak yang tersedia.')
                        ->action(function (Mahasiswa $record) {
                            $ta = $this->activeTa();

                            $tagihan = $ta
                                ? TagihanMahasiswa::where('mahasiswa_id', $record->id)
                                    ->where('tahun_akademik_id', $ta->id)
                                    ->latest()
                                    ->first()
                                : null;

                            $hasil = $tagihan
                                ? app(PaymentPolicyChecker::class)->cekKepatuhan($record, $tagihan)
                                : ['passed' => false, 'unmet' => []];

                            $terkirim = [];

                            if (! empty($record->person?->email)) {
                                try {
                                    Mail::to($record->person->email)
                                        ->queue(new CicilanTerverifikasiMailable($record, $hasil['unmet'] ?? []));
                                    $terkirim[] = 'email';
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            }

                            if (! empty($record->person?->no_hp)) {
                                try {
                                    app(SmsService::class)->send(
                                        $record->person->no_hp,
                                        'Silakan selesaikan tagihan untuk aktivasi NIM. Cek akun untuk detail.'
                                    );
                                    $terkirim[] = 'SMS';
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            }

                            if ($terkirim === []) {
                                Notification::make()
                                    ->title('Reminder tidak terkirim')
                                    ->body('Email/nomor HP belum tersedia atau pengiriman gagal.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Reminder dikirim')
                                ->body('Terkirim melalui: ' . implode(' dan ', $terkirim) . '.')
                                ->success()
                                ->send();
                        }),

                    Action::make('manual_generate_nim')
                        ->label('Generate NIM Manual')
                        ->icon('heroicon-o-identification')
                        ->color('success')
                        ->visible(fn (Mahasiswa $record) => str_starts_with((string) $record->nim, 'PMB'))
                        ->requiresConfirmation()
                        ->modalHeading('Generate NIM Manual')
                        ->modalDescription(fn (Mahasiswa $record) => $this->statusFor($record)['siap']
                            ? 'Mahasiswa ini sudah memenuhi persyaratan. NIM resmi akan dibuat.'
                            : 'PERHATIAN: Mahasiswa ini belum memenuhi persyaratan pembayaran. Generate manual akan melewati pemeriksaan kebijakan pembayaran.')
                        ->action(function (Mahasiswa $record) {
                            if (! str_starts_with((string) $record->nim, 'PMB')) {
                                Notification::make()
                                    ->title('Mahasiswa sudah memiliki NIM resmi')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            try {
                                $this->generateNim($record);
                            } catch (\Throwable $e) {
                                report($e);

                                Notification::make()
                                    ->title('NIM gagal dibuat')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('NIM berhasil dibuat')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('generate_nim_terpilih')
                        ->label('Generate NIM Terpilih')
                        ->icon('heroicon-o-identification')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Generate NIM Terpilih')
                        ->modalDescription('Hanya mahasiswa yang berstatus PMB dan memenuhi persyaratan yang akan diproses. Lainnya dilewati.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $status = $this->resolveStatuses($records->map->getKey()->all());

                            $berhasil = 0;
                            $dilewati = 0;
                            $gagal = 0;

                            foreach ($records as $record) {
                                $memenuhi = str_starts_with((string) $record->nim, 'PMB')
                                    && ($status[$record->getKey()]['siap'] ?? false);

                                if (! $memenuhi) {
                                    $dilewati++;

                                    continue;
                                }

                                try {
                                    $this->generateNim($record);
                                    $berhasil++;
                                } catch (\Throwable $e) {
                                    report($e);
                                    $gagal++;
                                }
                            }

                            Notification::make()
                                ->title('Proses Generate NIM selesai')
                                ->body("Berhasil: {$berhasil} | Dilewati: {$dilewati} | Gagal: {$gagal}")
                                ->color($gagal > 0 ? 'warning' : 'success')
                                ->send();
                        }),
                ]),
            ]);
    }
}