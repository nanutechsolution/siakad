<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Exports\PembimbingAkademikExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\PembimbingAkademik;
use App\Models\RefAngkatan;
use App\Support\Utf8;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class RiwayatPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationLabel = 'Riwayat Pembimbing Akademik';

    protected static ?string $modelLabel = 'Riwayat Pembimbing Akademik';

    protected string $view =
    'filament.clusters.pembimbing-akademik.pages.riwayat-pembimbing-page';

    protected static ?int $navigationSort = 2;

    protected static ?string $clusterBreadcrumb =
    'Riwayat Pembimbing Akademik';

    protected static ?string $title =
    'Riwayat Pembimbing Akademik';

    protected static ?string $description =
    'Gunakan pencarian dan filter untuk menemukan riwayat pembimbing berdasarkan mahasiswa, prodi, angkatan, kelas, dosen, jenis pembimbing, status, maupun periode penugasan.';

    protected static ?string $slug =
    'riwayat-pembimbing-akademik';

    protected static string|BackedEnum|null $navigationIcon =
    'heroicon-o-archive-box';

    protected static ?string $cluster =
    PembimbingAkademikCluster::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PembimbingAkademik::query()
                    ->with([
                        'mahasiswa.person',
                        'mahasiswa.angkatan',
                        'kelas.prodi',
                        'kelas.angkatan',
                        'dosen.person',
                    ])
                    ->withTrashed()
                    ->visibleTo(auth()->user())
            )

            /*
            |--------------------------------------------------------------------------
            | COLUMNS
            |--------------------------------------------------------------------------
            */
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | JENIS
                |--------------------------------------------------------------------------
                */
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(
                        fn(PembimbingAkademikJenis $state): string =>
                        $state->label()
                    )
                    ->tooltip(
                        fn(PembimbingAkademik $record): string =>
                        $record->jenis === PembimbingAkademikJenis::DOSEN_WALI
                            ? 'Penugasan dosen wali'
                            : 'Penugasan pembimbing/penguji'
                    ),

                /*
                |--------------------------------------------------------------------------
                | MAHASISWA
                |--------------------------------------------------------------------------
                */
                TextColumn::make('mahasiswa.nim')
                    ->label('Mahasiswa')
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->whereHas(
                                'mahasiswa',
                                function (Builder $mahasiswa) use ($search): void {
                                    $mahasiswa->where(
                                        'nim',
                                        'like',
                                        "%{$search}%"
                                    )->orWhereHas(
                                        'person',
                                        function (Builder $person) use ($search): void {
                                            $person->where(
                                                'nama_lengkap',
                                                'like',
                                                "%{$search}%"
                                            );
                                        }
                                    );
                                }
                            );
                        }
                    )
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIM berhasil disalin')
                    ->placeholder('-')
                    ->description(
                        fn(?PembimbingAkademik $record): string =>
                        Utf8::clean(
                            $record?->mahasiswa?->person?->nama_lengkap
                        ) ?: '-'
                    ),

                /*
                |--------------------------------------------------------------------------
                | ANGKATAN MAHASISWA
                |--------------------------------------------------------------------------
                */
                TextColumn::make('mahasiswa.angkatan.id_tahun')
                    ->label('Angkatan')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->placeholder('-'),

                /*
                |--------------------------------------------------------------------------
                | KELAS
                |--------------------------------------------------------------------------
                |
                | Ditampilkan:
                |
                | A
                | TI • 2024
                |
                | sehingga admin langsung tahu kelas tersebut milik prodi
                | dan angkatan mana.
                |--------------------------------------------------------------------------
                */
                TextColumn::make('kelas_info')
                    ->label('Kelas')
                    ->state(
                        function (PembimbingAkademik $record): string {
                            $kelas = $record->kelas;

                            if (! $kelas) {
                                return '-';
                            }

                            $namaKelas = Utf8::clean(
                                $kelas->nama_kelas
                            );

                            $kodeProdi = Utf8::clean(
                                $kelas->prodi?->kode_prodi_internal
                                    ?? '-'
                            );

                            $angkatan =
                                $kelas->angkatan?->id_tahun
                                ?? $kelas->angkatan_id
                                ?? '-';

                            return "{$namaKelas} — {$kodeProdi} — {$angkatan}";
                        }
                    )
                    ->description(
                        function (PembimbingAkademik $record): ?string {
                            if (! $record->kelas) {
                                return $record->mahasiswa?->angkatan?->id_tahun
                                    ? 'Kelas mahasiswa tidak tersimpan pada penugasan'
                                    : null;
                            }

                            return Utf8::clean(
                                $record->kelas->prodi?->nama_prodi
                            ) ?: null;
                        }
                    )
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->where(function (Builder $q) use ($search): void {

                                /*
                                | Penugasan langsung ke kelas
                                */
                                $q->whereHas(
                                    'kelas',
                                    function (Builder $kelas) use ($search): void {
                                        $kelas->where(
                                            'nama_kelas',
                                            'like',
                                            "%{$search}%"
                                        )->orWhereHas(
                                            'prodi',
                                            function (Builder $prodi) use ($search): void {
                                                $prodi
                                                    ->where(
                                                        'kode_prodi_internal',
                                                        'like',
                                                        "%{$search}%"
                                                    )
                                                    ->orWhere(
                                                        'nama_prodi',
                                                        'like',
                                                        "%{$search}%"
                                                    );
                                            }
                                        );
                                    }
                                )

                                    /*
                                | Penugasan per mahasiswa.
                                | Mahasiswa bisa mempunyai histori kelas.
                                */
                                    ->orWhereHas(
                                        'mahasiswa.kelas',
                                        function (Builder $kelas) use ($search): void {
                                            $kelas->where(
                                                'nama_kelas',
                                                'like',
                                                "%{$search}%"
                                            )->orWhereHas(
                                                'prodi',
                                                function (Builder $prodi) use ($search): void {
                                                    $prodi
                                                        ->where(
                                                            'kode_prodi_internal',
                                                            'like',
                                                            "%{$search}%"
                                                        )
                                                        ->orWhere(
                                                            'nama_prodi',
                                                            'like',
                                                            "%{$search}%"
                                                        );
                                                }
                                            );
                                        }
                                    );
                            });
                        }
                    )
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | PROGRAM STUDI
                |--------------------------------------------------------------------------
                */
                TextColumn::make('prodi_info')
                    ->label('Program Studi')
                    ->state(
                        function (PembimbingAkademik $record): string {
                            $prodi = $record->kelas?->prodi;

                            if ($prodi) {
                                $kode =
                                    $prodi->kode_prodi_internal
                                    ?? '-';

                                $nama =
                                    $prodi->nama_prodi
                                    ?? null;

                                return $nama
                                    ? "{$kode} — {$nama}"
                                    : $kode;
                            }

                            return $record->mahasiswa?->prodi?->nama_prodi
                                ?? '-';
                        }
                    )
                    ->placeholder('-')
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->where(function (Builder $q) use ($search): void {
                                $q->whereHas(
                                    'kelas.prodi',
                                    function (Builder $prodi) use ($search): void {
                                        $prodi
                                            ->where(
                                                'kode_prodi_internal',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhere(
                                                'nama_prodi',
                                                'like',
                                                "%{$search}%"
                                            );
                                    }
                                )->orWhereHas(
                                    'mahasiswa.prodi',
                                    function (Builder $prodi) use ($search): void {
                                        $prodi
                                            ->where(
                                                'kode_prodi_internal',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhere(
                                                'nama_prodi',
                                                'like',
                                                "%{$search}%"
                                            )
                                        ;
                                    }
                                );
                            });
                        }
                    )
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | DOSEN
                |--------------------------------------------------------------------------
                */
                TextColumn::make('dosen_info')
                    ->label('Dosen Pembimbing')
                    ->state(
                        fn(PembimbingAkademik $record): string =>
                        Utf8::clean(
                            $record->dosen?->person?->nama_lengkap
                        ) ?: '-'
                    )
                    ->description(
                        fn(?PembimbingAkademik $record): ?string =>
                        $record?->dosen?->nidn
                            ? 'NIDN: ' . $record->dosen->nidn
                            : null
                    )
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->whereHas(
                                'dosen',
                                function (Builder $dosen) use ($search): void {
                                    $dosen->where(
                                        'nidn',
                                        'like',
                                        "%{$search}%"
                                    )->orWhereHas(
                                        'person',
                                        function (Builder $person) use ($search): void {
                                            $person->where(
                                                'nama_lengkap',
                                                'like',
                                                "%{$search}%"
                                            );
                                        }
                                    );
                                }
                            );
                        }
                    )
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | PRIMARY
                |--------------------------------------------------------------------------
                */
                IconColumn::make('is_primary')
                    ->label('Utama')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(
                        fn(PembimbingAkademik $record): string =>
                        $record->is_primary
                            ? 'Pembimbing utama'
                            : 'Bukan pembimbing utama'
                    )
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | PERIODE
                |--------------------------------------------------------------------------
                */
                TextColumn::make('tanggal_mulai')
                    ->label('Periode Penugasan')
                    ->date('d M Y')
                    ->sortable()
                    ->description(
                        fn(?PembimbingAkademik $record): string =>
                        $record?->tanggal_selesai
                            ? 's/d ' . $record->tanggal_selesai->format('d M Y')
                            : 'Masih berjalan'
                    ),

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(
                        fn(PembimbingAkademikStatus $state): string =>
                        match ($state) {
                            PembimbingAkademikStatus::AKTIF => 'success',
                            PembimbingAkademikStatus::SELESAI => 'gray',
                            PembimbingAkademikStatus::DIBATALKAN => 'danger',
                        }
                    )
                    ->formatStateUsing(
                        fn(PembimbingAkademikStatus $state): string =>
                        $state->label()
                    ),

                /*
                |--------------------------------------------------------------------------
                | SK
                |--------------------------------------------------------------------------
                */
                TextColumn::make('nomor_sk')
                    ->label('SK')
                    ->placeholder('Belum ada SK')
                    ->tooltip(
                        fn(?string $state): ?string =>
                        $state ?: 'Nomor SK belum diisi'
                    )
                    ->copyable(
                        condition: fn(?string $state): bool =>
                        filled($state)
                    )
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | TANGGAL SK
                |--------------------------------------------------------------------------
                */
                TextColumn::make('tanggal_sk')
                    ->label('Tanggal SK')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | KETERANGAN
                |--------------------------------------------------------------------------
                */
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->tooltip(
                        fn(?string $state): ?string =>
                        $state
                    )
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                |--------------------------------------------------------------------------
                | INPUT
                |--------------------------------------------------------------------------
                */
                TextColumn::make('created_at')
                    ->label('Diinput')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                |--------------------------------------------------------------------------
                | DELETED
                |--------------------------------------------------------------------------
                */
                IconColumn::make('is_deleted')
                    ->label('Dihapus')
                    ->boolean()
                    ->getStateUsing(
                        fn(PembimbingAkademik $record): bool =>
                        $record->deleted_at !== null
                    )
                    ->trueColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /*
            |--------------------------------------------------------------------------
            | FILTERS
            |--------------------------------------------------------------------------
            */
            ->filters([

                /*
                |--------------------------------------------------------------------------
                | MAHASISWA
                |--------------------------------------------------------------------------
                */
                Filter::make('mahasiswa')
                    ->label('Mahasiswa')
                    ->schema([
                        TextInput::make('search')
                            ->label('NIM / Nama Mahasiswa')
                            ->placeholder('Contoh: 230101001 atau Budi Santoso')
                            ->prefixIcon('heroicon-o-magnifying-glass')
                            ->autocomplete(false),
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $search = trim($data['search'] ?? '');

                            if ($search === '') {
                                return $query;
                            }

                            return $query->whereHas(
                                'mahasiswa',
                                function (Builder $mahasiswa) use ($search): void {
                                    $mahasiswa
                                        ->where(
                                            'nim',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhereHas(
                                            'person',
                                            function (Builder $person) use ($search): void {
                                                $person->where(
                                                    'nama_lengkap',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                            }
                                        );
                                }
                            );
                        }
                    )
                    ->indicateUsing(
                        function (array $data): ?string {
                            $search = trim($data['search'] ?? '');

                            return $search
                                ? "Mahasiswa: {$search}"
                                : null;
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | PROGRAM STUDI
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->placeholder('Semua Program Studi')
                    ->options(
                        fn(): array =>
                        app(FormResolver::class)
                            ->prodiOptions(auth()->user())
                    )
                    ->searchable()
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $prodiId = $data['value'] ?? null;

                            if (! filled($prodiId)) {
                                return $query;
                            }

                            return $query->where(function (Builder $q) use ($prodiId): void {
                                $q->whereHas(
                                    'kelas',
                                    fn(Builder $kelas) =>
                                    $kelas->where('prodi_id', $prodiId)
                                )->orWhereHas(
                                    'mahasiswa',
                                    fn(Builder $mahasiswa) =>
                                    $mahasiswa->where('prodi_id', $prodiId)
                                );
                            });
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | ANGKATAN
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->placeholder('Semua Angkatan')
                    ->options(
                        fn(): array =>
                        RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck(
                                'id_tahun',
                                'id_tahun'
                            )
                            ->mapWithKeys(
                                fn($tahun) => [
                                    $tahun => 'Angkatan ' . $tahun,
                                ]
                            )
                            ->toArray()
                    )
                    ->searchable()
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $angkatanId = $data['value'] ?? null;

                            if (! filled($angkatanId)) {
                                return $query;
                            }

                            return $query->where(function (Builder $q) use ($angkatanId): void {
                                $q->whereHas(
                                    'mahasiswa',
                                    fn(Builder $mahasiswa) =>
                                    $mahasiswa->where(
                                        'angkatan_id',
                                        $angkatanId
                                    )
                                )->orWhereHas(
                                    'kelas',
                                    fn(Builder $kelas) =>
                                    $kelas->where(
                                        'angkatan_id',
                                        $angkatanId
                                    )
                                );
                            });
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | KELAS
                |--------------------------------------------------------------------------
                |
                | Hasil dropdown:
                |
                | A — TI — 2024
                | B — TI — 2024
                | A — SI — 2024
                |
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->placeholder('Semua Kelas')
                    ->options(
                        function (): array {
                            return Kelas::query()
                                ->with([
                                    'prodi',
                                    'angkatan',
                                ])
                                ->visibleTo(auth()->user())
                                ->orderByDesc('angkatan_id')
                                ->orderBy('prodi_id')
                                ->orderBy('nama_kelas')
                                ->get()
                                ->mapWithKeys(
                                    function (Kelas $kelas): array {
                                        $namaKelas = Utf8::clean(
                                            $kelas->nama_kelas
                                        );

                                        $kodeProdi = Utf8::clean(
                                            $kelas->prodi?->kode_prodi_internal
                                                ?? '-'
                                        );

                                        $angkatan =
                                            $kelas->angkatan?->id_tahun
                                            ?? $kelas->angkatan_id
                                            ?? '-';

                                        return [
                                            $kelas->id =>
                                            "{$namaKelas} — {$kodeProdi} — {$angkatan}",
                                        ];
                                    }
                                )
                                ->toArray();
                        }
                    )
                    ->searchable()
                    /*
                    | Jangan preload.
                    | Supaya dropdown tidak langsung menampilkan ratusan/ribuan kelas.
                    */
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $kelasId = $data['value'] ?? null;

                            if (! filled($kelasId)) {
                                return $query;
                            }

                            return $query->where(function (Builder $q) use ($kelasId): void {

                                /*
                                | Penugasan DOSEN_WALI per kelas
                                */
                                $q->where(
                                    'kelas_id',
                                    $kelasId
                                )

                                    /*
                                | Penugasan per mahasiswa:
                                | mahasiswa pernah/masih berada di kelas tersebut
                                */
                                    ->orWhereHas(
                                        'mahasiswa',
                                        function (Builder $mahasiswa) use ($kelasId): void {
                                            $mahasiswa->whereHas(
                                                'kelas',
                                                function (Builder $kelas) use ($kelasId): void {
                                                    $kelas->where(
                                                        'kelas.id',
                                                        $kelasId
                                                    );
                                                }
                                            );
                                        }
                                    );
                            });
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | DOSEN
                |--------------------------------------------------------------------------
                */
                Filter::make('dosen')
                    ->label('Dosen Pembimbing')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextInput::make('search')
                            ->label('Nama Dosen / NIDN')
                            ->placeholder('Contoh: Dr. Budi atau 0123456789')
                            ->prefixIcon('heroicon-o-magnifying-glass')
                            ->autocomplete(false),
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $search = trim($data['search'] ?? '');

                            if ($search === '') {
                                return $query;
                            }

                            return $query->whereHas(
                                'dosen',
                                function (Builder $dosen) use ($search): void {
                                    $dosen
                                        ->where(
                                            'nidn',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhereHas(
                                            'person',
                                            function (Builder $person) use ($search): void {
                                                $person->where(
                                                    'nama_lengkap',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                            }
                                        );
                                }
                            );
                        }
                    )
                    ->indicateUsing(
                        function (array $data): ?string {
                            $search = trim($data['search'] ?? '');

                            return $search
                                ? "Dosen: {$search}"
                                : null;
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | JENIS
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('jenis')
                    ->label('Jenis Penugasan')
                    ->placeholder('Semua Jenis')
                    ->options(
                        PembimbingAkademikJenis::options()
                    ),

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('status')
                    ->label('Status Penugasan')
                    ->placeholder('Semua Status')
                    ->options(
                        PembimbingAkademikStatus::options()
                    ),

                /*
                |--------------------------------------------------------------------------
                | PEMBIMBING UTAMA
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('is_primary')
                    ->label('Pembimbing')
                    ->placeholder('Utama & Pendamping')
                    ->options([
                        1 => 'Utama',
                        0 => 'Bukan Utama',
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            if ($data['value'] === null || $data['value'] === '') {
                                return $query;
                            }

                            return $query->where(
                                'is_primary',
                                (int) $data['value']
                            );
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | SK
                |--------------------------------------------------------------------------
                */
                SelectFilter::make('sk')
                    ->label('Dokumen SK')
                    ->placeholder('Semua')
                    ->options([
                        'ada' => 'Sudah ada SK',
                        'belum' => 'Belum ada SK',
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $value = $data['value'] ?? null;

                            if (! filled($value)) {
                                return $query;
                            }

                            return match ($value) {
                                'ada' => $query->whereNotNull('nomor_sk')
                                    ->where('nomor_sk', '!=', ''),

                                'belum' => $query->where(function (Builder $q): void {
                                    $q->whereNull('nomor_sk')
                                        ->orWhere('nomor_sk', '');
                                }),

                                default => $query,
                            };
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | PERIODE TANGGAL MULAI
                |--------------------------------------------------------------------------
                */
                Filter::make('tanggal_mulai')
                    ->label('Tanggal Mulai Penugasan')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        DatePicker::make('dari')
                            ->label('Dari')
                            ->native(false),

                        DatePicker::make('sampai')
                            ->label('Sampai')
                            ->native(false),
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $dari = $data['dari'] ?? null;
                            $sampai = $data['sampai'] ?? null;

                            return $query
                                ->when(
                                    filled($dari),
                                    fn(Builder $q) =>
                                    $q->whereDate(
                                        'tanggal_mulai',
                                        '>=',
                                        $dari
                                    )
                                )
                                ->when(
                                    filled($sampai),
                                    fn(Builder $q) =>
                                    $q->whereDate(
                                        'tanggal_mulai',
                                        '<=',
                                        $sampai
                                    )
                                );
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | NOMOR SK
                |--------------------------------------------------------------------------
                */
                Filter::make('nomor_sk')
                    ->label('Nomor SK')
                    ->schema([
                        TextInput::make('search')
                            ->label('Cari Nomor SK')
                            ->placeholder('Contoh: 123/UNIV/2024')
                            ->prefixIcon('heroicon-o-document-text')
                            ->autocomplete(false),
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            $search = trim($data['search'] ?? '');

                            if ($search === '') {
                                return $query;
                            }

                            return $query->where(
                                'nomor_sk',
                                'like',
                                "%{$search}%"
                            );
                        }
                    )
                    ->indicateUsing(
                        function (array $data): ?string {
                            $search = trim($data['search'] ?? '');

                            return $search
                                ? "SK: {$search}"
                                : null;
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | DATA DIHAPUS
                |--------------------------------------------------------------------------
                */
                TrashedFilter::make()
                    ->label('Data Dihapus'),
            ])

            /*
            |--------------------------------------------------------------------------
            | HEADER ACTIONS
            |--------------------------------------------------------------------------
            */
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(
                        fn() => Excel::download(
                            new PembimbingAkademikExport(
                                PembimbingAkademik::query()
                                    ->with([
                                        'mahasiswa.person',
                                        'mahasiswa.angkatan',
                                        'kelas.prodi',
                                        'kelas.angkatan',
                                        'dosen.person',
                                    ])
                                    ->withTrashed()
                            ),
                            'riwayat-pembimbing-' .
                                now()->format('Ymd-His') .
                                '.xlsx'
                        )
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | EMPTY STATE
            |--------------------------------------------------------------------------
            */
            ->emptyStateHeading(
                'Belum ada riwayat penugasan'
            )
            ->emptyStateDescription(
                'Belum ditemukan data pembimbing akademik sesuai filter yang dipilih.'
            )
            ->emptyStateIcon(
                'heroicon-o-magnifying-glass'
            )

            /*
            |--------------------------------------------------------------------------
            | RECORD ACTIONS
            |--------------------------------------------------------------------------
            */
            ->recordActions([

                /*
                | Cetak SK
                */
                Action::make('cetakSk')
                    ->label('Cetak SK')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(
                        fn(PembimbingAkademik $record): bool =>
                        ! $record->trashed()
                    )
                    ->url(
                        fn(PembimbingAkademik $record): string =>
                        route(
                            'pembimbing-akademik.sk',
                            $record
                        )
                    )
                    ->openUrlInNewTab(),

                /*
                | Pulihkan data
                */
                Action::make('pulihkan')
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(
                        fn(PembimbingAkademik $record): bool =>
                        $record->trashed()
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Pulihkan Riwayat Pembimbing'
                    )
                    ->modalDescription(
                        'Data riwayat ini akan dimunculkan kembali dalam daftar riwayat.'
                    )
                    ->action(
                        function (PembimbingAkademik $record): void {
                            $record->restore();

                            Notification::make()
                                ->title(
                                    'Data berhasil dipulihkan'
                                )
                                ->body(
                                    'Riwayat pembimbing telah dikembalikan.'
                                )
                                ->success()
                                ->send();
                        }
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | DEFAULT SORT
            |--------------------------------------------------------------------------
            */
            ->defaultSort(
                'created_at',
                'desc'
            );
    }
}
