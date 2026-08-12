<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Domain\Authorization\Services\OrganizationResolver;
use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Exports\PembimbingAkademikExport;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Support\Utf8;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
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
    protected string $view = 'filament.clusters.pembimbing-akademik.pages.riwayat-pembimbing-page';
    protected static ?int $navigationSort = 2;
    protected static ?string $clusterBreadcrumb = 'Riwayat Pembimbing Akademik';
    protected static ?string $title = 'Riwayat Pembimbing Akademik';
    protected static ?string $description = 'Halaman ini menampilkan riwayat pembimbing akademik yang telah ditugaskan kepada mahasiswa. Anda dapat melihat daftar pembimbing, periode penugasan, dan informasi terkait lainnya. Halaman ini membantu dalam memantau dan mengelola penugasan pembimbing akademik secara efektif.';
    protected static ?string $slug = 'riwayat-pembimbing-akademik';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $cluster = PembimbingAkademikCluster::class;


    public function table(Table $table): Table
    {
        return $table
            ->query(
                PembimbingAkademik::query()
                    ->with([
                        'mahasiswa.person',
                        'mahasiswa.angkatan',
                        'kelas',
                        'dosen.person',
                    ])
                    ->withTrashed()
                    ->visibleTo(auth()->user())
            )
            ->columns([
                // =========================================================
                // JENIS PEMBIMBING
                // =========================================================
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(
                        fn(PembimbingAkademikJenis $state) => $state->label()
                    )
                    ->sortable(),

                // =========================================================
                // MAHASISWA
                // =========================================================
                TextColumn::make('mahasiswa_info')
                    ->label('Mahasiswa')
                    ->getStateUsing(function (PembimbingAkademik $record): string {
                        if (! $record->mahasiswa) {
                            return 'Penugasan Per Kelas';
                        }

                        return trim(
                            ($record->mahasiswa->nim ?? '-') .
                                ' — ' .
                                Utf8::clean(
                                    $record->mahasiswa->person?->nama_lengkap ?? '-'
                                )
                        );
                    })
                    ->description(function (PembimbingAkademik $record): ?string {
                        if (! $record->mahasiswa) {
                            return $record->kelas
                                ? 'Seluruh mahasiswa pada kelas'
                                : null;
                        }

                        return 'Angkatan ' .
                            ($record->mahasiswa->angkatan?->id_tahun ?? '-');
                    })
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('mahasiswa', function (Builder $q) use ($search) {
                                $q->where('nim', 'like', "%{$search}%")
                                    ->orWhereHas('person', function (Builder $person) use ($search) {
                                        $person->where(
                                            'nama_lengkap',
                                            'like',
                                            "%{$search}%"
                                        );
                                    });
                            });
                        }
                    )
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->orderBy(
                            Mahasiswa::query()
                                ->select('nim')
                                ->whereColumn(
                                    'mahasiswas.id',
                                    'pembimbing_akademik.mahasiswa_id'
                                ),
                            $direction
                        );
                    }),

                // =========================================================
                // PRODI
                // =========================================================
                TextColumn::make('prodi_info')
                    ->label('Program Studi')
                    ->getStateUsing(function (PembimbingAkademik $record): string {
                        $prodi = $record->mahasiswa?->prodi
                            ?? $record->kelas?->prodi;

                        if (! $prodi) {
                            return '-';
                        }

                        $kode = $prodi->kode_prodi_internal
                            ?? $prodi->kode_prodi_internal
                            ?? null;

                        $nama = $prodi->nama_prodi
                            ?? $prodi->nama
                            ?? '-';

                        return $kode
                            ? "{$kode} — " . Utf8::clean($nama)
                            : Utf8::clean($nama);
                    })
                    ->wrap()
                    ->toggleable(),

                // =========================================================
                // ANGKATAN
                // =========================================================
                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->getStateUsing(function (PembimbingAkademik $record): ?string {
                        return $record->mahasiswa?->angkatan?->id_tahun
                            ?? $record->kelas?->angkatan?->id_tahun;
                    })
                    ->badge()
                    ->color('gray')
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->orderBy(
                            Mahasiswa::query()
                                ->select('angkatan_id')
                                ->whereColumn(
                                    'mahasiswas.id',
                                    'pembimbing_akademik.mahasiswa_id'
                                ),
                            $direction
                        );
                    }),

                // =========================================================
                // KELAS
                // =========================================================
                TextColumn::make('kelas_info')
                    ->label('Kelas')
                    ->getStateUsing(function (PembimbingAkademik $record): string {
                        $kelas = $record->kelas;

                        if (! $kelas) {
                            return $record->mahasiswa
                                ? 'Per Mahasiswa'
                                : '-';
                        }

                        $namaKelas = Utf8::clean($kelas->nama_kelas);

                        $kodeProdi = $kelas->prodi?->kode_prodi_internal
                            ?? $kelas->prodi?->kode_prodi_internal
                            ?? '-';

                        $angkatan = $kelas->angkatan?->id_tahun ?? '-';

                        return "{$namaKelas} — {$kodeProdi} — {$angkatan}";
                    })
                    ->description(function (PembimbingAkademik $record): ?string {
                        if (! $record->kelas) {
                            return $record->mahasiswa
                                ? 'Penugasan langsung ke mahasiswa'
                                : null;
                        }

                        return $record->kelas->prodi
                            ? Utf8::clean(
                                $record->kelas->prodi->nama_prodi
                                    ?? $record->kelas->prodi->nama
                                    ?? ''
                            )
                            : null;
                    })
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('kelas', function (Builder $q) use ($search) {
                                $q->where('nama_kelas', 'like', "%{$search}%")
                                    ->orWhereHas('prodi', function (Builder $prodi) use ($search) {
                                        $prodi->where('kode_prodi_internal', 'like', "%{$search}%")
                                            ->orWhere('nama_prodi', 'like', "%{$search}%");
                                    });
                            });
                        }
                    )
                    ->wrap(),

                // =========================================================
                // DOSEN
                // =========================================================
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
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('dosen', function (Builder $q) use ($search) {
                                $q->where('nidn', 'like', "%{$search}%")
                                    ->orWhereHas('person', function (Builder $person) use ($search) {
                                        $person->where(
                                            'nama_lengkap',
                                            'like',
                                            "%{$search}%"
                                        );
                                    });
                            });
                        }
                    ),

                // =========================================================
                // CAKUPAN
                // =========================================================
                TextColumn::make('cakupan')
                    ->label('Cakupan')
                    ->getStateUsing(
                        fn(PembimbingAkademik $record) =>
                        $record->kelas_id
                            ? 'Per Kelas'
                            : 'Per Mahasiswa'
                    )
                    ->badge()
                    ->color(
                        fn(string $state) =>
                        $state === 'Per Kelas'
                            ? 'info'
                            : 'warning'
                    ),

                // =========================================================
                // PRIMARY
                // =========================================================
                IconColumn::make('is_primary')
                    ->label('Utama')
                    ->boolean()
                    ->tooltip(
                        fn(PembimbingAkademik $record) =>
                        $record->is_primary
                            ? 'Pembimbing utama'
                            : 'Bukan pembimbing utama'
                    )
                    ->toggleable(),

                // =========================================================
                // PERIODE
                // =========================================================
                TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(function (PembimbingAkademik $record): string {
                        $mulai = $record->tanggal_mulai
                            ? \Carbon\Carbon::parse($record->tanggal_mulai)
                            ->translatedFormat('d M Y')
                            : '-';

                        $selesai = $record->tanggal_selesai
                            ? \Carbon\Carbon::parse($record->tanggal_selesai)
                            ->translatedFormat('d M Y')
                            : 'Sekarang';

                        return "{$mulai} s/d {$selesai}";
                    })
                    ->description(
                        fn(PembimbingAkademik $record) =>
                        $record->tanggal_sk
                            ? 'SK: ' . \Carbon\Carbon::parse($record->tanggal_sk)
                            ->translatedFormat('d M Y')
                            : null
                    )
                    ->sortable(),

                // =========================================================
                // STATUS
                // =========================================================
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(PembimbingAkademikStatus $state) => match ($state) {
                        PembimbingAkademikStatus::AKTIF => 'success',
                        PembimbingAkademikStatus::SELESAI => 'gray',
                        PembimbingAkademikStatus::DIBATALKAN => 'danger',
                    })
                    ->formatStateUsing(
                        fn(PembimbingAkademikStatus $state) => $state->label()
                    )
                    ->sortable(),

                // =========================================================
                // SK
                // =========================================================
                TextColumn::make('nomor_sk')
                    ->label('No. SK')
                    ->placeholder('Belum ada SK')
                    ->copyable()
                    ->toggleable(),

                // =========================================================
                // INPUT
                // =========================================================
                TextColumn::make('created_at')
                    ->label('Diinput')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // =========================================================
                // DIHAPUS
                // =========================================================
                IconColumn::make('is_deleted')
                    ->label('Dihapus')
                    ->boolean()
                    ->getStateUsing(
                        fn(PembimbingAkademik $record) =>
                        $record->deleted_at !== null
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->where(fn(Builder $q) => $q
                            ->whereHas('kelas', fn(Builder $k) => $k->where('prodi_id', $data['value']))
                            ->orWhereHas('mahasiswa', fn(Builder $m) => $m->where('prodi_id', $data['value'])));
                    }),
                // =========================================================
                // ANGKATAN
                // =========================================================
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->placeholder('Semua angkatan')
                    ->options(
                        fn(): array => RefAngkatan::query()
                            ->orderByDesc('id_tahun')
                            ->pluck('id_tahun', 'id_tahun')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        $angkatanId = $data['value'] ?? null;

                        if (! filled($angkatanId)) {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($angkatanId) {

                            // Assignment per kelas
                            $q->whereHas('kelas', function (Builder $kelas) use ($angkatanId) {
                                $kelas->where('angkatan_id', $angkatanId);
                            })
                                // Assignment per mahasiswa
                                ->orWhereHas('mahasiswa', function (Builder $mahasiswa) use ($angkatanId) {
                                    $mahasiswa->where('angkatan_id', $angkatanId);
                                });
                        });
                    }),

                // ==========================================
                // DOSEN
                // ==========================================
                Filter::make('dosen')
                    ->label('Dosen Pembimbing')
                    ->schema([
                        TextInput::make('search')
                            ->label('Nama Dosen / NIDN')
                            ->placeholder('Cari nama atau NIDN')
                            ->prefixIcon('heroicon-o-user'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $search = trim($data['search'] ?? '');

                        if ($search === '') {
                            return $query;
                        }

                        return $query->whereHas('dosen', function (Builder $q) use ($search) {
                            $q->where('nidn', 'like', "%{$search}%")
                                ->orWhereHas('person', function (Builder $person) use ($search) {
                                    $person->where(
                                        'nama_lengkap',
                                        'like',
                                        "%{$search}%"
                                    );
                                });
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $search = trim($data['search'] ?? '');

                        return $search
                            ? "Dosen: {$search}"
                            : null;
                    }),

                // ==========================================
                // KELAS
                // ==========================================
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->placeholder('Semua kelas')
                    ->options(function (): array {
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
                            ->mapWithKeys(function (Kelas $kelas): array {
                                $namaKelas = Utf8::clean($kelas->nama_kelas);

                                $kodeProdi = $kelas->prodi?->kode_prodi_internal
                                    ?? $kelas->prodi?->kode_prodi_internal
                                    ?? '-';

                                $namaProdi = $kelas->prodi?->nama_prodi
                                    ?? $kelas->prodi?->nama
                                    ?? 'Program Studi';

                                $angkatan = $kelas->angkatan?->id_tahun
                                    ?? $kelas->angkatan_id
                                    ?? '-';

                                $label = sprintf(
                                    '%s — %s (%s) — %s',
                                    $namaKelas,
                                    Utf8::clean($namaProdi),
                                    $kodeProdi,
                                    $angkatan
                                );

                                return [
                                    $kelas->id => $label,
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        $kelasId = $data['value'] ?? null;

                        if (! filled($kelasId)) {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($kelasId) {

                            // Assignment langsung ke kelas
                            $q->where('kelas_id', $kelasId)

                                // Assignment per mahasiswa yang pernah berada
                                // di kelas tersebut
                                ->orWhereHas('mahasiswa', function (Builder $mahasiswa) use ($kelasId) {
                                    $mahasiswa->whereHas('kelas', function (Builder $kelas) use ($kelasId) {
                                        $kelas->where('kelas.id', $kelasId);
                                    });
                                });
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! filled($data['value'] ?? null)) {
                            return null;
                        }

                        $kelas = Kelas::with(['prodi', 'angkatan'])
                            ->find($data['value']);

                        if (! $kelas) {
                            return null;
                        }

                        $kode = $kelas->prodi?->kode_prodi_internal
                            ?? $kelas->prodi?->kode_prodi_internal
                            ?? '-';

                        return 'Kelas: ' .
                            Utf8::clean($kelas->nama_kelas) .
                            ' — ' .
                            $kode .
                            ' — ' .
                            ($kelas->angkatan?->id_tahun ?? '-');
                    }),
                SelectFilter::make('jenis')
                    ->options(PembimbingAkademikJenis::options()),
                SelectFilter::make('status')
                    ->options(PembimbingAkademikStatus::options()),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn() => Excel::download(
                        new PembimbingAkademikExport(PembimbingAkademik::query()->withTrashed()),
                        'riwayat-pembimbing-' . now()->format('Ymd-His') . '.xlsx'
                    )),
            ])
            ->emptyStateHeading('Belum ada riwayat penugasan')
            ->emptyStateDescription('Riwayat akan muncul di sini setelah ada penugasan pembimbing yang dibuat.')
            ->emptyStateIcon('heroicon-o-clock')
            ->recordActions([
                Action::make('cetakSk')
                    ->label('Cetak SK')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn(PembimbingAkademik $record) => ! $record->trashed())
                    ->url(
                        fn(PembimbingAkademik $record): string =>
                        route('pembimbing-akademik.sk', $record)
                    )
                    ->openUrlInNewTab(),
                Action::make('pulihkan')
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn(PembimbingAkademik $record) => $record->trashed())
                    ->requiresConfirmation()
                    ->modalDescription('Data riwayat ini akan dimunculkan kembali di daftar aktif/histori.')
                    ->action(function (PembimbingAkademik $record): void {
                        $record->restore();

                        Notification::make()
                            ->title('Data berhasil dipulihkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
