<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Exceptions\PembimbingAkademikException;
use App\Exports\PembimbingAkademikExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\PembimbingAkademik;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikPdfService;
use App\Services\PembimbingAkademikService;
use App\Support\Utf8;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class MutasiPembimbingPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;
    protected string $view = 'filament.clusters.pembimbing-akademik.pages.mutasi-pembimbing-page';
    protected static ?string $navigationLabel = 'Mutasi Pembimbing Akademik';
    protected static ?string $modelLabel = 'Mutasi Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Mutasi Pembimbing Akademik';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Mutasi Pembimbing Akademik';
    protected static ?string $description = 'Halaman ini digunakan untuk melakukan mutasi atau perubahan pembimbing akademik bagi mahasiswa. Anda dapat memindahkan mahasiswa dari satu pembimbing ke pembimbing lainnya, serta mengelola catatan terkait mutasi tersebut. Halaman ini membantu dalam memastikan penugasan pembimbing akademik tetap sesuai dengan kebutuhan mahasiswa dan kebijakan akademik.';
    protected static ?string $slug = 'mutasi-pembimbing-akademik';
    protected static ?string $cluster = PembimbingAkademikCluster::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected function dosenSelectField(string $name, string $label, ?string $excludeDosenId = null): Select
    {
        return Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                ->when($excludeDosenId, fn($q) => $q->where('id', '!=', $excludeDosenId))
                ->where(fn($q) => $q
                    ->where('nidn', 'like', "%{$search}%")
                    ->orWhereHas('person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")))
                ->limit(20)
                ->get()
                ->mapWithKeys(fn(TrxDosen $d) => [$d->id => Utf8::clean("{$d->person?->nama_lengkap} ({$d->nidn})")]))
            ->getOptionLabelUsing(fn($value) => optional(TrxDosen::find($value))?->nidn)
            ->required();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PembimbingAkademik::query()
                    ->with([
                        'mahasiswa.person',
                        'mahasiswa.prodi',
                        'mahasiswa.kelas',
                        'kelas',
                        'dosen.person',
                        'semesterMulai',
                    ])
            )

            ->columns([
                /*
             |--------------------------------------------------------------------------
             | TARGET
             |--------------------------------------------------------------------------
             */
                TextColumn::make('target')
                    ->label('Target Penugasan')
                    ->state(function (PembimbingAkademik $record): string {
                        if ($record->kelas_id) {
                            return 'Kelas: ' . (
                                $record->kelas?->nama_kelas ?? '-'
                            );
                        }

                        return $record->mahasiswa?->nim
                            ? $record->mahasiswa->nim . ' — ' .
                            ($record->mahasiswa->person?->nama_lengkap ?? '-')
                            : '-';
                    })
                    ->description(function (PembimbingAkademik $record): ?string {
                        if ($record->kelas_id) {
                            return 'Penugasan berbasis kelas';
                        }

                        return $record->mahasiswa?->prodi?->nama_prodi
                            ? 'Prodi: ' . $record->mahasiswa->prodi->nama_prodi
                            : null;
                    })
                    ->searchable(query: function (
                        Builder $query,
                        string $search
                    ): Builder {
                        return $query
                            ->where(function (Builder $q) use ($search) {
                                $q
                                    ->whereHas(
                                        'mahasiswa',
                                        fn(Builder $m) => $m
                                            ->where(
                                                'nim',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhereHas(
                                                'person',
                                                fn(Builder $p) => $p->where(
                                                    'nama_lengkap',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                            )
                                    )
                                    ->orWhereHas(
                                        'kelas',
                                        fn(Builder $k) => $k->where(
                                            'nama_kelas',
                                            'like',
                                            "%{$search}%"
                                        )
                                    );
                            });
                    })
                    ->sortable(),

                /*
             |--------------------------------------------------------------------------
             | PRODI
             |--------------------------------------------------------------------------
             */
                TextColumn::make('mahasiswa.prodi.nama_prodi')
                    ->label('Program Studi')
                    ->placeholder('-')
                    ->toggleable(),

                /*
             |--------------------------------------------------------------------------
             | ANGKATAN
             |--------------------------------------------------------------------------
             */
                TextColumn::make('mahasiswa.angkatan_id')
                    ->label('Angkatan')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                /*
             |--------------------------------------------------------------------------
             | KELAS
             |--------------------------------------------------------------------------
             */
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->placeholder('-')
                    ->formatStateUsing(
                        fn(?string $state) => $state
                            ? Utf8::clean($state)
                            : null
                    )
                    ->sortable()
                    ->toggleable(),

                /*
             |--------------------------------------------------------------------------
             | MAHASISWA
             |--------------------------------------------------------------------------
             */
                TextColumn::make('mahasiswa.nim')
                    ->label('Mahasiswa')
                    ->placeholder('-')
                    ->description(
                        fn(?PembimbingAkademik $record) =>
                        $record?->mahasiswa?->person?->nama_lengkap
                            ? Utf8::clean(
                                $record->mahasiswa->person->nama_lengkap
                            )
                            : null
                    )
                    ->searchable(query: function (
                        Builder $query,
                        string $search
                    ): Builder {
                        return $query->whereHas(
                            'mahasiswa',
                            fn(Builder $q) => $q
                                ->where(
                                    'nim',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'person',
                                    fn(Builder $p) => $p->where(
                                        'nama_lengkap',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                        );
                    })
                    ->sortable(),

                /*
             |--------------------------------------------------------------------------
             | DOSEN
             |--------------------------------------------------------------------------
             */
                TextColumn::make('dosen_nama')
                    ->label('Dosen Pembimbing')
                    ->getStateUsing(
                        fn(PembimbingAkademik $record) =>
                        Utf8::clean(
                            $record->dosen?->person?->nama_lengkap ?? '-'
                        )
                    )
                    ->description(
                        fn(?PembimbingAkademik $record) =>
                        $record?->dosen?->nidn
                            ? 'NIDN: ' . $record->dosen->nidn
                            : null
                    )
                    ->searchable(query: function (
                        Builder $query,
                        string $search
                    ): Builder {
                        return $query->whereHas(
                            'dosen',
                            fn(Builder $q) => $q
                                ->where(
                                    'nidn',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'person',
                                    fn(Builder $p) => $p->where(
                                        'nama_lengkap',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                        );
                    })
                    ->sortable(),

                /*
             |--------------------------------------------------------------------------
             | JENIS
             |--------------------------------------------------------------------------
             */
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(
                        fn(PembimbingAkademikJenis $state) =>
                        $state->label()
                    )
                    ->sortable(),

                /*
             |--------------------------------------------------------------------------
             | SEMESTER
             |--------------------------------------------------------------------------
             */
                TextColumn::make('semesterMulai.nama_tahun')
                    ->label('Semester Mulai')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                /*
             |--------------------------------------------------------------------------
             | TANGGAL
             |--------------------------------------------------------------------------
             */
                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                /*
             |--------------------------------------------------------------------------
             | SK
             |--------------------------------------------------------------------------
             */
                TextColumn::make('nomor_sk')
                    ->label('SK')
                    ->placeholder('Belum ada SK')
                    ->description(
                        fn(?PembimbingAkademik $record) =>
                        $record?->tanggal_sk
                            ? 'Tanggal: ' . $record->tanggal_sk->format('d M Y')
                            : null
                    )
                    ->searchable()
                    ->toggleable(),

                /*
             |--------------------------------------------------------------------------
             | STATUS
             |--------------------------------------------------------------------------
             */
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn(PembimbingAkademikStatus $state) =>
                        $state->label()
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
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),
            ])

            /*
         |--------------------------------------------------------------------------
         | FILTER
         |--------------------------------------------------------------------------
         */
            ->filters([

                /*
             * Program Studi
             */
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(
                        RefProdi::query()
                            ->orderBy('nama_prodi')
                            ->pluck('nama_prodi', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->multiple()
                    ->query(function (
                        Builder $query,
                        array $data
                    ): Builder {
                        $values = $data['values'] ?? [];

                        if (empty($values)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'mahasiswa',
                            fn(Builder $q) =>
                            $q->whereIn('prodi_id', $values)
                        );
                    }),

                /*
             * Angkatan
             */
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(
                        RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck('id_tahun', 'id_tahun')
                            ->toArray()
                    )
                    ->searchable()
                    ->multiple()
                    ->query(function (
                        Builder $query,
                        array $data
                    ): Builder {
                        $values = $data['values'] ?? [];

                        if (empty($values)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'mahasiswa',
                            fn(Builder $q) =>
                            $q->whereIn('angkatan_id', $values)
                        );
                    }),

                /*
             * Kelas
             */
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        return Kelas::query()
                            ->with([
                                'prodi',
                            ])
                            ->orderBy('nama_kelas')
                            ->get()
                            ->mapWithKeys(function (Kelas $kelas) {
                                $label = $kelas->nama_kelas;

                                $detail = collect([
                                    $kelas->prodi?->nama_prodi,
                                    $kelas->angkatan_id
                                        ? 'Angkatan ' . $kelas->angkatan_id
                                        : null,
                                ])
                                    ->filter()
                                    ->implode(' • ');

                                if ($detail !== '') {
                                    $label .= ' — ' . $detail;
                                }

                                return [
                                    $kelas->id => $label,
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple()
                    ->preload(),

                /*
             * Dosen
             */
                SelectFilter::make('dosen_id')
                    ->label('Dosen Pembimbing')
                    ->relationship('dosen', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn(TrxDosen $record) =>
                        Utf8::clean(
                            ($record->person?->nama_lengkap ?? '-') .
                                ' (' .
                                ($record->nidn ?? '-') .
                                ')'
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->multiple(),

                /*
             * Jenis
             */
                SelectFilter::make('jenis')
                    ->label('Jenis Pembimbing')
                    ->options(PembimbingAkademikJenis::options())
                    ->multiple(),

                /*
             * Semester
             */
                SelectFilter::make('semester_mulai_id')
                    ->label('Semester Mulai')
                    ->relationship(
                        'semesterMulai',
                        'nama_tahun'
                    )
                    ->searchable()
                    ->preload()
                    ->multiple(),

                /*
             * Status
             */
                SelectFilter::make('status')
                    ->label('Status Penugasan')
                    ->options(
                        collect(PembimbingAkademikStatus::cases())
                            ->mapWithKeys(
                                fn($case) => [
                                    $case->value => $case->label(),
                                ]
                            )
                            ->toArray()
                    )
                    ->multiple()
                    ->default([
                        PembimbingAkademikStatus::AKTIF->value,
                    ]),

                /*
             * Primary
             */
                SelectFilter::make('is_primary')
                    ->label('Pembimbing')
                    ->options([
                        1 => 'Pembimbing Utama',
                        0 => 'Pembimbing Tambahan',
                    ]),

                /*
             * Status SK
             */
                SelectFilter::make('status_sk')
                    ->label('Status SK')
                    ->options([
                        'ada' => 'Sudah memiliki SK',
                        'belum' => 'Belum memiliki SK',
                    ])
                    ->query(function (
                        Builder $query,
                        array $data
                    ): Builder {
                        return match ($data['value'] ?? null) {
                            'ada' => $query
                                ->whereNotNull('nomor_sk')
                                ->where('nomor_sk', '!=', ''),

                            'belum' => $query->where(
                                function (Builder $q) {
                                    $q
                                        ->whereNull('nomor_sk')
                                        ->orWhere(
                                            'nomor_sk',
                                            ''
                                        );
                                }
                            ),

                            default => $query,
                        };
                    }),

                /*
             * Periode Tanggal Mulai
             */
                Filter::make('tanggal_mulai')
                    ->label('Tanggal Penugasan')
                    ->schema([
                        DatePicker::make('dari')
                            ->label('Dari'),

                        DatePicker::make('sampai')
                            ->label('Sampai'),
                    ])
                    ->columns(2)
                    ->query(function (
                        Builder $query,
                        array $data
                    ): Builder {
                        return $query
                            ->when(
                                $data['dari'] ?? null,
                                fn(
                                    Builder $q,
                                    $date
                                ) => $q->whereDate(
                                    'tanggal_mulai',
                                    '>=',
                                    $date
                                )
                            )
                            ->when(
                                $data['sampai'] ?? null,
                                fn(
                                    Builder $q,
                                    $date
                                ) => $q->whereDate(
                                    'tanggal_mulai',
                                    '<=',
                                    $date
                                )
                            );
                    }),

            ])

            /*
         |--------------------------------------------------------------------------
         | HEADER ACTION
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
                            ),
                            'pembimbing-' .
                                now()->format('Ymd-His') .
                                '.xlsx'
                        )
                    ),
            ])

            /*
         |--------------------------------------------------------------------------
         | RECORD ACTION
         |--------------------------------------------------------------------------
         */
            ->recordActions([
                Action::make('cetakSk')
                    ->label('Cetak SK')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(
                        fn(PembimbingAkademik $record) =>
                        filled($record->nomor_sk)
                    )
                    ->action(
                        fn(PembimbingAkademik $record) =>
                        app(
                            PembimbingAkademikPdfService::class
                        )->downloadSkPenugasan($record)
                    ),

                Action::make('mutasi')
                    ->label('Mutasi')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->slideOver()
                    ->modalHeading(
                        fn(PembimbingAkademik $record) =>
                        'Mutasi Pembimbing: ' .
                            Utf8::clean(
                                $record->mahasiswa?->nim ??
                                    $record->kelas?->nama_kelas ??
                                    '-'
                            )
                    )
                    ->schema(
                        fn(PembimbingAkademik $record) => [
                            $this->dosenSelectField(
                                'dosen_id',
                                'Dosen Pengganti',
                                $record->dosen_id
                            )
                                ->helperText(
                                    'Dosen yang sedang aktif tidak muncul di pilihan.'
                                ),

                            DatePicker::make('tanggal_mulai')
                                ->label('Tanggal Mulai Penugasan Baru')
                                ->default(now())
                                ->minDate($record->tanggal_mulai)
                                ->helperText(
                                    'Tidak boleh lebih awal dari tanggal penugasan saat ini (' .
                                        optional($record->tanggal_mulai)
                                        ->format('d M Y') .
                                        ').'
                                )
                                ->required(),

                            Select::make('semester_mulai_id')
                                ->label('Semester Mulai')
                                ->searchable()
                                ->options(
                                    fn() =>
                                    RefTahunAkademik::query()
                                        ->orderByDesc('id')
                                        ->pluck(
                                            'nama_tahun',
                                            'id'
                                        )
                                )
                                ->required(),

                            TextInput::make('nomor_sk')
                                ->label('Nomor SK Mutasi')
                                ->maxLength(255),

                            DatePicker::make('tanggal_sk')
                                ->label('Tanggal SK'),

                            Textarea::make('alasan')
                                ->label('Alasan Mutasi')
                                ->rows(2)
                                ->required()
                                ->minLength(5),
                        ]
                    )
                    ->requiresConfirmation()
                    ->modalDescription(
                        fn(PembimbingAkademik $record) =>
                        new HtmlString(
                            'Penugasan saat ini (<strong>' .
                                e(
                                    Utf8::clean(
                                        $record->dosen?->person?->nama_lengkap
                                    )
                                ) .
                                '</strong>) akan ditutup otomatis dan diganti dosen baru. ' .
                                'Riwayat tetap tersimpan.'
                        )
                    )
                    ->action(
                        function (
                            array $data,
                            PembimbingAkademik $record
                        ): void {
                            try {
                                app(
                                    PembimbingAkademikService::class
                                )->mutasi($record, $data);

                                Notification::make()
                                    ->title(
                                        'Mutasi pembimbing berhasil disimpan'
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                PembimbingAkademikException $e
                            ) {
                                Notification::make()
                                    ->title(
                                        'Tidak bisa memproses mutasi'
                                    )
                                    ->body($e->getMessage())
                                    ->warning()
                                    ->send();
                            }
                        }
                    ),

                Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->slideOver()
                    ->form([
                        Textarea::make('alasan')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->minLength(5)
                            ->rows(2),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Batalkan Penugasan Pembimbing'
                    )
                    ->modalDescription(
                        'Penugasan akan diakhiri tanpa pengganti. Status berubah menjadi Dibatalkan, data tidak dihapus.'
                    )
                    ->action(
                        function (
                            array $data,
                            PembimbingAkademik $record
                        ): void {
                            app(
                                PembimbingAkademikService::class
                            )->batalkan(
                                $record,
                                $data['alasan']
                            );

                            Notification::make()
                                ->title(
                                    'Penugasan berhasil dibatalkan'
                                )
                                ->success()
                                ->send();
                        }
                    ),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mutasiMassal')
                        ->label('Mutasi Massal')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->color('warning')
                        ->slideOver()
                        ->form([
                            $this->dosenSelectField(
                                'dosen_id',
                                'Dosen Pengganti'
                            ),

                            DatePicker::make('tanggal_mulai')
                                ->label(
                                    'Tanggal Mulai Penugasan Baru'
                                )
                                ->default(now())
                                ->required(),

                            Select::make('semester_mulai_id')
                                ->label('Semester Mulai')
                                ->searchable()
                                ->options(
                                    fn() =>
                                    RefTahunAkademik::query()
                                        ->orderByDesc('id')
                                        ->pluck(
                                            'nama_tahun',
                                            'id'
                                        )
                                )
                                ->required(),

                            Textarea::make('alasan')
                                ->label('Alasan Mutasi')
                                ->required()
                                ->minLength(5)
                                ->rows(2),
                        ])
                        ->requiresConfirmation()
                        ->modalDescription(
                            'Semua baris terpilih akan dimutasi ke dosen yang sama.'
                        )
                        ->action(
                            function (
                                Collection $records,
                                array $data
                            ): void {
                                $service =
                                    app(
                                        PembimbingAkademikService::class
                                    );

                                $berhasil = 0;
                                $gagal = 0;

                                foreach ($records as $record) {
                                    try {
                                        $service->mutasi(
                                            $record,
                                            $data
                                        );

                                        $berhasil++;
                                    } catch (
                                        PembimbingAkademikException) {
                                        $gagal++;
                                    }
                                }

                                Notification::make()
                                    ->title(
                                        'Mutasi massal selesai'
                                    )
                                    ->body(
                                        "{$berhasil} berhasil, {$gagal} dilewati."
                                    )
                                    ->success()
                                    ->persistent()
                                    ->send();
                            }
                        )
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('batalkanMassal')
                        ->label('Batalkan Massal')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->slideOver()
                        ->form([
                            Textarea::make('alasan')
                                ->label('Alasan Pembatalan')
                                ->required()
                                ->minLength(5)
                                ->rows(2),
                        ])
                        ->requiresConfirmation()
                        ->modalDescription(
                            'Semua baris terpilih akan dibatalkan sekaligus.'
                        )
                        ->action(
                            function (
                                Collection $records,
                                array $data
                            ): void {
                                $service =
                                    app(
                                        PembimbingAkademikService::class
                                    );

                                foreach ($records as $record) {
                                    $service->batalkan(
                                        $record,
                                        $data['alasan']
                                    );
                                }

                                Notification::make()
                                    ->title(
                                        $records->count() .
                                            ' penugasan berhasil dibatalkan'
                                    )
                                    ->success()
                                    ->send();
                            }
                        )
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])

            ->emptyStateHeading(
                'Tidak ada penugasan pembimbing'
            )
            ->emptyStateDescription(
                'Tidak ditemukan data berdasarkan filter yang dipilih.'
            )
            ->emptyStateIcon(
                'heroicon-o-user-group'
            )
            ->defaultSort(
                'tanggal_mulai',
                'desc'
            );
    }
}
