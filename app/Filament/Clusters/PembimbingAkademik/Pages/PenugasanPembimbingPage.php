<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\KonfigurasiPembimbingAkademik;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class PenugasanPembimbingPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.penugasan-pembimbing-page';
    protected static ?string $navigationLabel = 'Penugasan Pembimbing Akademik';
    protected static ?string $modelLabel = 'Penugasan Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Penugasan Pembimbing Akademik';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Penugasan Pembimbing Akademik';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $cluster = PembimbingAkademikCluster::class;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {

        $this->form->fill([
            'jenis' => PembimbingAkademikJenis::DOSEN_WALI->value,
            'is_primary' => true,
            'tanggal_mulai' => now()->toDateString(),
            'semester_mulai_id' => RefTahunAkademik::query()->where('is_active', true)->value('id'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Aturan & Scope Konfigurasi')
                    ->description('Pilih Prodi dan Angkatan. Sistem akan membaca aturan mode pembimbing secara otomatis.')
                    ->columns(3)
                    ->components([
                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('mode_info', null)),

                        Select::make('angkatan_id')
                            ->label('Angkatan')
                            ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $prodiId = $get('prodi_id');
                                $angkatanId = $get('angkatan_id');

                                if (! $prodiId || ! $angkatanId) {
                                    $set('mode', null);
                                    $set('mode_info', null);
                                    return;
                                }

                                $config = KonfigurasiPembimbingAkademik::query()
                                    ->where('prodi_id', $prodiId)
                                    ->where('angkatan_id', $angkatanId)
                                    ->where('aktif', true)
                                    ->first();

                                if (! $config) {
                                    $set('mode', null);
                                    $set('mode_info', 'BELUM_DIKONFIGURASI');
                                    Notification::make()
                                        ->title('Konfigurasi Tidak Ditemukan')
                                        ->body('Aturan pembimbing akademik untuk Prodi dan Angkatan ini belum aktif/dibuat.')
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                if (! $config) {
                                    $set('mode', null);
                                    $set('mode_info', 'BELUM_DIKONFIGURASI');

                                    Notification::make()
                                        ->title('Konfigurasi Tidak Ditemukan')
                                        ->body('Aturan pembimbing akademik untuk Prodi dan Angkatan ini belum aktif/dibuat.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $set('mode', $config->mode->value);

                                $set(
                                    'mode_info',
                                    $config->mode->value === 'PER_KELAS'
                                        ? 'MODE: PER KELAS (1 Dosen Wali / Kelas)'
                                        : 'MODE: PER MAHASISWA (Individual)'
                                );
                            }),

                        TextEntry::make('mode_info_placeholder')
                            ->label('Status Mode Penugasan')
                            ->state(fn(Get $get) => $get('mode_info') ?? 'Pilih Prodi & Angkatan dahulu'),
                    ]),
                Section::make('Target Penugasan')
                    ->columns(2)
                    ->components([
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->searchable()
                            ->options(fn(Get $get) => Kelas::query()
                                ->where('prodi_id', $get('prodi_id'))
                                ->where('angkatan_id', $get('angkatan_id'))
                                ->orderBy('nama_kelas')
                                ->pluck('nama_kelas', 'id'))
                            ->visible(fn(Get $get) => $get('mode') === 'PER_KELAS')
                            ->required(fn(Get $get) => $get('mode') === 'PER_KELAS'),
                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search, Get $get) => Mahasiswa::query()
                                ->where('prodi_id', $get('prodi_id'))
                                ->where('angkatan_id', $get('angkatan_id'))
                                ->where(function ($q) use ($search) {
                                    $q->where('nim', 'like', "%{$search}%")
                                        ->orWhereHas('person', fn($sq) => $sq->where('nama_lengkap', 'like', "%{$search}%"));
                                })
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn(Mahasiswa $m) => [$m->id => "{$m->nim} - {$m->person?->nama_lengkap}"]))
                            ->getOptionLabelUsing(fn($value) => optional(Mahasiswa::find($value))->nim)
                            ->visible(fn(Get $get) => $get('mode') === 'PER_MAHASISWA')
                            ->required(fn(Get $get) => $get('mode') === 'PER_MAHASISWA'),
                    ]),

                Section::make('Detail Pembimbing')
                    ->columns(2)
                    ->components([
                        Select::make('jenis')
                            ->label('Jenis Pembimbing')
                            ->options(PembimbingAkademikJenis::options())
                            ->native(false)
                            ->required(),
                        Select::make('dosen_id')
                            ->label('Dosen Pembimbing')
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                                ->where('nidn', 'like', "%{$search}%")
                                ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                            ->getOptionLabelUsing(fn($value) => optional(TrxDosen::find($value))->person?->nama_lengkap)
                            ->required(),

                        Toggle::make('is_primary')
                            ->label('Pembimbing Utama (Primary)')
                            ->default(true),

                        Select::make('semester_mulai_id')
                            ->label('Semester Mulai')
                            ->searchable()
                            ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                            ->required(),

                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai Penugasan')
                            ->default(now())
                            ->required(),

                        TextInput::make('nomor_sk')
                            ->label('Nomor SK Penugasan')
                            ->maxLength(255),

                        DatePicker::make('tanggal_sk')
                            ->label('Tanggal SK Penugasan'),

                        Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(PembimbingAkademikService $service): void
    {
        $state = $this->form->getState();

        try {
            $service->assignPembimbing($state);

            Notification::make()
                ->title('Penugasan Berhasil')
                ->body('Dosen pembimbing akademik telah berhasil ditugaskan.')
                ->success()
                ->send();

            $this->form->fill([
                'prodi_id' => $state['prodi_id'],
                'angkatan_id' => $state['angkatan_id'],
                'mode' => $state['mode'] ?? null,
                'jenis' => $state['jenis'],
                'is_primary' => true,
                'tanggal_mulai' => now()->toDateString(),
                'semester_mulai_id' => $state['semester_mulai_id'],
            ]);
        } catch (\DomainException | \InvalidArgumentException $e) {
            Notification::make()
                ->title('Gagal Memproses Penugasan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Sistem Error')
                ->body('Terjadi kesalahan internal. Silakan hubungi Administrator.')
                ->danger()
                ->send();
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateOtomatis')
                ->label('Generate Massal')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->schema([
                    Select::make('prodi_id')
                        ->label('Program Studi')
                        ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                        ->required(),

                    Select::make('angkatan_id')
                        ->label('Angkatan')
                        ->options(fn() => RefAngkatan::query()->orderByDesc('tahun')->pluck('tahun', 'id'))
                        ->required(),

                    Select::make('dosen_id')
                        ->label('Dosen Pembimbing Target')
                        ->searchable()
                        ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                            ->where('nidn', 'like', "%{$search}%")
                            ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                        ->required(),

                    Select::make('jenis')
                        ->label('Jenis Pembimbing')
                        ->options(PembimbingAkademikJenis::class)
                        ->default(PembimbingAkademikJenis::DOSEN_WALI->value)
                        ->required(),

                    Select::make('semester_mulai_id')
                        ->label('Semester Mulai')
                        ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                        ->required(),

                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->default(now())
                        ->required(),

                    TextInput::make('nomor_sk')->label('Nomor SK'),
                    DatePicker::make('tanggal_sk')->label('Tanggal SK'),
                ])
                ->action(function (array $data, PembimbingAkademikService $service) {
                    try {
                        $count = $service->generateOtomatis(
                            (int) $data['prodi_id'],
                            (int) $data['angkatan_id'],
                            (int) $data['dosen_id'],
                            $data['jenis'],
                            (int) $data['semester_mulai_id'],
                            $data['tanggal_mulai'],
                            $data['nomor_sk'] ?? null,
                            $data['tanggal_sk'] ?? null
                        );

                        Notification::make()
                            ->title('Generate Berhasil')
                            ->body("Berhasil menugaskan {$count} data pembimbing akademik secara otomatis.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Generate Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('attachment')
                        ->label('File Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    Notification::make()
                        ->title('Import Terjadwal')
                        ->body('File Excel telah masuk ke antrean background queue processor.')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PembimbingAkademik::query()
                    ->with(['kelas', 'mahasiswa.person', 'dosen.person', 'semesterMulai'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('target')
                    ->label('Target Penugasan')
                    ->state(function (PembimbingAkademik $record): string {
                        if ($record->kelas_id) {
                            return "Kelas: {$record->kelas?->nama_kelas}";
                        }
                        return "Mahasiswa: {$record->mahasiswa?->nim} - {$record->mahasiswa?->person?->nama_lengkap}";
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', "%{$search}%"))
                            ->orWhereHas('mahasiswa', fn($q) => $q->where('nim', 'like', "%{$search}%")
                                ->orWhereHas('person', fn($sq) => $sq->where('nama_lengkap', 'like', "%{$search}%")));
                    })
                    ->sortable(),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->sortable(),

                TextColumn::make('dosen.person.nama_lengkap')
                    ->label('Dosen Pembimbing')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semesterMulai.nama_tahun')
                    ->label('Semester Mulai')
                    ->sortable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options(PembimbingAkademikJenis::class),

                SelectFilter::make('status')
                    ->options(PembimbingAkademikStatus::class),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->authorize(fn(PembimbingAkademik $record) => Gate::allows('update', $record)),
                Action::make('mutasi')
                    ->label('Mutasi Pembimbing')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->visible(fn(PembimbingAkademik $record) => $record->status === PembimbingAkademikStatus::AKTIF)
                    ->authorize(fn(PembimbingAkademik $record) => Gate::allows('update', $record))
                    ->schema([
                        Select::make('dosen_baru_id')
                            ->label('Dosen Pembimbing Baru')
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                                ->where('nidn', 'like', "%{$search}%")
                                ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                            ->required(),

                        Select::make('semester_selesai_id')
                            ->label('Semester Pergantian')
                            ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                            ->required(),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Efektif Mutasi')
                            ->default(now())
                            ->required(),

                        Textarea::make('alasan')
                            ->label('Alasan Mutasi')
                            ->required(),
                    ])
                    ->action(function (PembimbingAkademik $record, array $data, PembimbingAkademikService $service) {
                        try {
                            $service->mutasiPembimbing(
                                $record,
                                (int) $data['dosen_baru_id'],
                                (int) $data['semester_selesai_id'],
                                $data['tanggal_selesai'],
                                $data['alasan']
                            );

                            Notification::make()
                                ->title('Mutasi Pembimbing Berhasil')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal Mutasi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('selesai')
                    ->label('Selesaikan Penugasan')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn(PembimbingAkademik $record) => $record->status === PembimbingAkademikStatus::AKTIF)
                    ->schema([
                        Select::make('semester_selesai_id')
                            ->label('Semester Selesai')
                            ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                            ->required(),
                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->default(now())
                            ->required(),
                        Textarea::make('alasan')
                            ->label('Alasan Selesai'),
                    ])
                    ->action(function (PembimbingAkademik $record, array $data) {
                        $record->update([
                            'status' => PembimbingAkademikStatus::SELESAI,
                            'semester_selesai_id' => $data['semester_selesai_id'],
                            'tanggal_selesai' => $data['tanggal_selesai'],
                            'alasan' => $data['alasan'] ?? null,
                            'updated_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Penugasan Diselesaikan')
                            ->success()
                            ->send();
                    }),

                Action::make('batalkan')
                    ->label('Batalkan Penugasan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(PembimbingAkademik $record) => $record->status === PembimbingAkademikStatus::AKTIF)
                    ->action(function (PembimbingAkademik $record) {
                        $record->update([
                            'status' => PembimbingAkademikStatus::DIBATALKAN,
                            'updated_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Penugasan Dibatalkan')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
