<?php

namespace App\Filament\Clusters\ManajemenKelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Exceptions\ManajemenKelasException;
use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Services\Kelas\ManajemenKelasService;
use App\Support\Utf8;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PenempatanMahasiswaPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;
    protected static string|\BackedEnum|null $navigationIcon =
    'heroicon-o-user-group';

    protected static ?string $navigationLabel =
    'Penempatan Mahasiswa';

    protected static ?string $title =
    'Penempatan & Mutasi Mahasiswa ke Kelas';

    protected static ?int $navigationSort = 3;

    protected string $view =
    'filament.clusters.manajemen-kelas.pages.penempatan-mahasiswa-page';

    protected static ?string $cluster =
    ManajemenKelasCluster::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleTo(auth()->user());
    }

    protected function kelasSelectField(
        string $name = 'kelas_id',
        string $label = 'Kelas Tujuan'
    ): Select {
        return Select::make($name)
            ->label($label)
            ->searchable()
            ->preload()

            /*
         * Kelas hanya boleh berasal dari Prodi
         * yang dapat diakses oleh user.
         */
            ->getSearchResultsUsing(function (string $search): array {
                $user = auth()->user();

                if (! $user) {
                    return [];
                }

                $resolver = app(FormResolver::class);

                $prodiIds = $resolver->accessibleProdiIds($user);

                if ($prodiIds === []) {
                    return [];
                }

                return Kelas::query()
                    ->with('prodi')
                    ->whereIn('prodi_id', $prodiIds)
                    ->where(function (Builder $query) use ($search) {
                        $query
                            ->where('nama_kelas', 'like', "%{$search}%")
                            ->orWhere('angkatan_id', 'like', "%{$search}%")
                            ->orWhereHas(
                                'prodi',
                                fn(Builder $q) => $q
                                    ->where(
                                        'kode_prodi_internal',
                                        'like',
                                        "%{$search}%"
                                    )
                            );
                    })
                    ->orderByDesc('angkatan_id')
                    ->orderBy('nama_kelas')
                    ->limit(30)
                    ->get()
                    ->mapWithKeys(function (Kelas $kelas): array {
                        $service = app(ManajemenKelasService::class);

                        $jumlah = $service->jumlahAnggotaAktif($kelas->id);

                        $kapasitas = $kelas->kapasitas !== null
                            ? "/{$kelas->kapasitas}"
                            : '';

                        $kodeProdi = $kelas->prodi?->kode_prodi_internal
                            ? Utf8::clean($kelas->prodi->kode_prodi_internal)
                            : '-';

                        $label = sprintf(
                            '%s — %s — Angkatan %s (%d%s)',
                            $kodeProdi,
                            Utf8::clean($kelas->nama_kelas),
                            $kelas->angkatan_id,
                            $jumlah,
                            $kapasitas
                        );

                        return [
                            $kelas->id => $label,
                        ];
                    })
                    ->all();
            })

            /*
         * Label untuk value yang sudah terpilih.
         *
         * Tetap divalidasi terhadap Prodi yang boleh diakses user.
         */
            ->getOptionLabelUsing(function ($value): ?string {
                if (blank($value)) {
                    return null;
                }

                $user = auth()->user();

                if (! $user) {
                    return null;
                }

                $resolver = app(FormResolver::class);

                $prodiIds = $resolver->accessibleProdiIds($user);

                if ($prodiIds === []) {
                    return null;
                }

                $kelas = Kelas::query()
                    ->with('prodi')
                    ->whereIn('prodi_id', $prodiIds)
                    ->find($value);

                if (! $kelas) {
                    return null;
                }

                $service = app(ManajemenKelasService::class);

                $jumlah = $service->jumlahAnggotaAktif($kelas->id);

                $kapasitas = $kelas->kapasitas !== null
                    ? "/{$kelas->kapasitas}"
                    : '';

                $kodeProdi = $kelas->prodi?->kode_prodi_internal
                    ? Utf8::clean($kelas->prodi->kode_prodi_internal)
                    : '-';

                return sprintf(
                    '%s — %s — Angkatan %s (%d%s)',
                    $kodeProdi,
                    Utf8::clean($kelas->nama_kelas),
                    $kelas->angkatan_id,
                    $jumlah,
                    $kapasitas
                );
            })
            ->required();
    }



    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $user = auth()->user();
                $resolver = app(FormResolver::class);

                $prodiIds =
                    $resolver->accessibleProdiIds($user);

                return Mahasiswa::query()
                    ->whereNull('deleted_at')
                    ->whereIn('prodi_id', $prodiIds);
            })
            ->deselectAllRecordsWhenFiltered(false)
            ->columns([
                TextColumn::make('nim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->sortable()
                    ->getStateUsing(
                        fn(Mahasiswa $record) => Utf8::clean(
                            $record->person?->nama_lengkap
                        )
                    )
                    ->searchable(
                        query: fn(
                            Builder $query,
                            string $search
                        ) => $query->whereHas(
                            'person',
                            fn($q) => $q->where(
                                'nama_lengkap',
                                'like',
                                "%{$search}%"
                            )
                        )
                    ),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(
                        fn(?string $state) =>
                        $state
                            ? Utf8::clean($state)
                            : null
                    ),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable(),

                TextColumn::make('kelas_aktif')
                    ->label('Kelas Saat Ini')
                    ->getStateUsing(
                        function (Mahasiswa $record) {
                            $aktif = app(
                                ManajemenKelasService::class
                            )->keanggotaanAktif($record->id);

                            return $aktif
                                ? Utf8::clean(
                                    $aktif->kelas?->nama_kelas
                                )
                                : null;
                        }
                    )
                    ->badge()
                    ->color(
                        fn(?string $state) =>
                        $state
                            ? 'success'
                            : 'danger'
                    )
                    ->placeholder('Belum ada kelas'),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(
                        fn() =>
                        app(FormResolver::class)
                            ->prodiOptions(auth()->user())
                    ),

                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(
                        fn() =>
                        RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck(
                                'id_tahun',
                                'id_tahun'
                            )
                    ),

                SelectFilter::make('kelas_id')
                    ->label('Kelas Saat Ini')
                    ->searchable()
                    ->options(function () {
                        $user = auth()->user();

                        $resolver =
                            app(FormResolver::class);

                        $prodiIds =
                            $resolver
                            ->accessibleProdiIds($user);

                        if ($prodiIds === []) {
                            return [];
                        }

                        return Kelas::query()
                            ->whereIn(
                                'prodi_id',
                                $prodiIds
                            )
                            ->orderBy(
                                'angkatan_id',
                                'desc'
                            )
                            ->orderBy('nama_kelas')
                            ->get()
                            ->mapWithKeys(
                                fn(Kelas $kelas) => [
                                    $kelas->id =>
                                    sprintf(
                                        '%s — Angkatan %s',
                                        Utf8::clean(
                                            $kelas->nama_kelas
                                        ),
                                        $kelas->angkatan_id
                                    ),
                                ]
                            )
                            ->all();
                    })
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            if (
                                blank(
                                    $data['value'] ?? null
                                )
                            ) {
                                return $query;
                            }

                            return $query->whereHas(
                                'mahasiswaKelas',
                                fn($q) =>
                                $q
                                    ->where(
                                        'kelas_id',
                                        $data['value']
                                    )
                                    ->whereNull(
                                        'tanggal_keluar'
                                    )
                            );
                        }
                    ),

                TernaryFilter::make('punya_kelas')
                    ->label('Status Kelas')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah punya kelas')
                    ->falseLabel('Belum punya kelas')
                    ->queries(
                        true: fn(Builder $query) =>
                        $query->whereHas(
                            'mahasiswaKelas',
                            fn($q) =>
                            $q->whereNull(
                                'tanggal_keluar'
                            )
                        ),

                        false: fn(Builder $query) =>
                        $query->whereDoesntHave(
                            'mahasiswaKelas',
                            fn($q) =>
                            $q->whereNull(
                                'tanggal_keluar'
                            )
                        ),
                    ),
            ])
            ->recordActions([
                Action::make('tempatkanAtauPindahkan')
                    ->label(
                        fn(Mahasiswa $record) =>
                        app(
                            ManajemenKelasService::class
                        )->keanggotaanAktif($record->id)
                            ? 'Pindahkan'
                            : 'Tempatkan'
                    )
                    ->icon(
                        'heroicon-o-arrow-right-circle'
                    )
                    ->color(
                        fn(Mahasiswa $record) =>
                        app(
                            ManajemenKelasService::class
                        )->keanggotaanAktif($record->id)
                            ? 'warning'
                            : 'success'
                    )
                    ->slideOver()
                    ->schema([
                        $this->kelasSelectField(),

                        DatePicker::make(
                            'tanggal_masuk'
                        )
                            ->label('Tanggal Masuk')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(
                        function (
                            array $data,
                            Mahasiswa $record
                        ): void {
                            try {
                                $service =
                                    app(
                                        ManajemenKelasService::class
                                    );

                                $keanggotaan =
                                    $service->tempatkan(
                                        $record->id,
                                        (int) $data['kelas_id'],
                                        $data['tanggal_masuk']
                                    );

                                $cek =
                                    $service
                                    ->cekKonsistensiKelas(
                                        (int) $keanggotaan->kelas_id
                                    );

                                $kelasNama =
                                    Utf8::clean(
                                        $keanggotaan
                                            ->kelas
                                            ?->nama_kelas
                                    );

                                if (
                                    $cek['status']
                                    === 'KELAS_TANPA_WALI'
                                ) {
                                    Notification::make()
                                        ->title(
                                            'Mahasiswa berhasil dipindahkan'
                                        )
                                        ->body(
                                            "Mahasiswa berhasil ditempatkan di {$kelasNama}, tetapi kelas tersebut belum memiliki Dosen Wali aktif."
                                        )
                                        ->warning()
                                        ->persistent()
                                        ->send();

                                    return;
                                }

                                if (
                                    $cek['status']
                                    === 'BELUM_KONFIGURASI'
                                ) {
                                    Notification::make()
                                        ->title(
                                            'Mahasiswa berhasil dipindahkan'
                                        )
                                        ->body(
                                            "Kelas {$kelasNama} berhasil digunakan, tetapi konfigurasi Pembimbing Akademik untuk Prodi dan Angkatan tersebut belum ditentukan."
                                        )
                                        ->warning()
                                        ->persistent()
                                        ->send();

                                    return;
                                }

                                Notification::make()
                                    ->title(
                                        'Mahasiswa berhasil ditempatkan'
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                ManajemenKelasException $e
                            ) {
                                Notification::make()
                                    ->title(
                                        'Gagal menempatkan'
                                    )
                                    ->body(
                                        $e->getMessage()
                                    )
                                    ->warning()
                                    ->send();
                            }
                        }
                    ),

                Action::make('keluarkan')
                    ->label(
                        'Keluarkan dari Kelas'
                    )
                    ->icon(
                        'heroicon-o-x-circle'
                    )
                    ->color('danger')
                    ->visible(
                        fn(Mahasiswa $record) =>
                        app(
                            ManajemenKelasService::class
                        )->keanggotaanAktif(
                            $record->id
                        ) !== null
                    )
                    ->requiresConfirmation()
                    ->modalDescription(
                        'Mahasiswa akan dikeluarkan dari kelas saat ini tanpa dipindah ke kelas lain.'
                    )
                    ->action(
                        function (
                            Mahasiswa $record
                        ): void {
                            $service =
                                app(
                                    ManajemenKelasService::class
                                );

                            $aktif =
                                $service->keanggotaanAktif(
                                    $record->id
                                );

                            if ($aktif) {
                                $service
                                    ->keluarkanDariKelas(
                                        $aktif
                                    );
                            }

                            Notification::make()
                                ->title(
                                    'Mahasiswa dikeluarkan dari kelas'
                                )
                                ->success()
                                ->send();
                        }
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make(
                        'tempatkanMassal'
                    )
                        ->label(
                            'Tempatkan/Pindahkan Massal'
                        )
                        ->icon(
                            'heroicon-o-arrow-right-circle'
                        )
                        ->color('success')
                        ->slideOver()
                        ->schema([
                            $this->kelasSelectField(
                                'kelas_id',
                                'Kelas Tujuan (berlaku untuk semua baris terpilih)'
                            ),

                            DatePicker::make(
                                'tanggal_masuk'
                            )
                                ->label('Tanggal Masuk')
                                ->default(now())
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->modalDescription(
                            'Semua mahasiswa terpilih akan ditempatkan/dipindahkan ke kelas yang sama. Baris yang gagal akan dilewati dan dilaporkan.'
                        )
                        ->action(
                            function (
                                Collection $records,
                                array $data
                            ): void {
                                $service =
                                    app(
                                        ManajemenKelasService::class
                                    );

                                $berhasil = 0;
                                $gagal = 0;
                                $warningWali = false;
                                $warningKonfigurasi = false;

                                foreach (
                                    $records as $record
                                ) {
                                    try {
                                        $keanggotaan =
                                            $service->tempatkan(
                                                $record->id,
                                                (int) $data['kelas_id'],
                                                $data['tanggal_masuk']
                                            );

                                        $berhasil++;

                                        $cek =
                                            $service
                                            ->cekKonsistensiKelas(
                                                (int) $keanggotaan->kelas_id
                                            );

                                        if (
                                            $cek['status']
                                            === 'KELAS_TANPA_WALI'
                                        ) {
                                            $warningWali = true;
                                        }

                                        if (
                                            $cek['status']
                                            === 'BELUM_KONFIGURASI'
                                        ) {
                                            $warningKonfigurasi = true;
                                        }
                                    } catch (
                                        ManajemenKelasException) {
                                        $gagal++;
                                    }
                                }

                                $body =
                                    "{$berhasil} berhasil, {$gagal} gagal.";

                                if ($warningWali) {
                                    $body .=
                                        ' Kelas tujuan belum memiliki Dosen Wali aktif.';
                                }

                                if (
                                    $warningKonfigurasi
                                ) {
                                    $body .=
                                        ' Konfigurasi Pembimbing Akademik belum ditentukan.';
                                }

                                Notification::make()
                                    ->title(
                                        'Penempatan massal selesai'
                                    )
                                    ->body($body)
                                    ->color(
                                        $warningWali ||
                                            $warningKonfigurasi
                                            ? 'warning'
                                            : 'success'
                                    )
                                    ->persistent()
                                    ->send();
                            }
                        )
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading(
                'Tidak ada data mahasiswa'
            )
            ->defaultSort('nim');
    }
}
