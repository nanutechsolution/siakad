<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use App\Exports\Kelas\MahasiswaKelasExport;
use App\Exports\Kelas\MahasiswaKelasImport as KelasMahasiswaKelasImport;
use App\Imports\Kelas\MahasiswaKelasImport;
use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use App\Services\MahasiswaPlottingService;
use App\Services\MahasiswaMutasiService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'mahasiswaKelas';
    protected static ?string $title = 'Anggota Kelas';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['mahasiswa.person']))
            ->columns([
                TextColumn::make('mahasiswa.nim')->label('NIM')->searchable()->sortable(),
                TextColumn::make('mahasiswa.person.nama_lengkap')->label('Nama')->searchable()->wrap(),
                TextColumn::make('tanggal_masuk')->date('d M Y'),
                TextColumn::make('tanggal_keluar')->date('d M Y')->placeholder('Aktif'),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn(MahasiswaKelas $record) => $record->tanggal_keluar === null ? 'AKTIF' : 'NONAKTIF')
                    ->color(fn($state) => $state === 'AKTIF' ? 'success' : 'gray'),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn() => Excel::download(
                        new MahasiswaKelasExport($this->getOwnerRecord()),
                        'kelas-' . $this->getOwnerRecord()->id . '.xlsx'
                    )),

                // 2. Import Excel Action
                Action::make('import_excel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->schema([
                        FileUpload::make('file')
                            ->label('File Excel (.xlsx)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->storeFiles(false),
                    ])
                    ->action(function (array $data, MahasiswaPlottingService $service) {
                        try {
                            DB::beginTransaction();

                            Excel::import(
                                new KelasMahasiswaKelasImport($this->getOwnerRecord()->id, $service),
                                $data['file']
                            );

                            DB::commit();

                            Notification::make()
                                ->title('Import Berhasil')
                                ->body('Data mahasiswa berhasil diplot ke dalam kelas.')
                                ->success()
                                ->send();
                        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                            DB::rollBack();
                            $failures = $e->failures();
                            $errorMessage = collect($failures)->map(fn($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))->implode('<br>');

                            Notification::make()
                                ->title('Gagal Validasi Import')
                                ->body($errorMessage)
                                ->danger()
                                ->persistent()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Import Error: ' . $e->getMessage());

                            Notification::make()
                                ->title('Import Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('plot_mahasiswa')
                    ->label('Plotting Mahasiswa')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading('Plotting Mahasiswa ke Kelas')
                    ->modalDescription(function () {
                        $kelas = $this->getOwnerRecord();

                        return "Tambahkan mahasiswa ke kelas {$kelas->nama_kelas}. "
                            . "Mahasiswa dapat berasal dari angkatan berbeda jika memang merupakan "
                            . "kasus restart, cuti, atau penempatan khusus.";
                    })
                    ->modalWidth('3xl')
                    ->schema([

                        /*
        |--------------------------------------------------------------------------
        | INFORMASI KELAS TUJUAN
        |--------------------------------------------------------------------------
        */

                        Section::make('Informasi Kelas Tujuan')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([

                                TextEntry::make('kelas_tujuan')
                                    ->label('Kelas')
                                    ->state(function () {
                                        $kelas = $this->getOwnerRecord();

                                        return $kelas->nama_kelas;
                                    })
                                    ->weight('bold'),

                                TextEntry::make('angkatan_kelas')
                                    ->label('Angkatan Kelas')
                                    ->state(function () {
                                        return $this->getOwnerRecord()->angkatan_id;
                                    })
                                    ->badge(),

                                TextEntry::make('kapasitas_kelas')
                                    ->label('Kapasitas')
                                    ->state(function () {
                                        $kelas = $this->getOwnerRecord();

                                        return $kelas->kapasitas ?? 'Tidak dibatasi';
                                    }),

                                TextEntry::make('jumlah_mahasiswa')
                                    ->label('Mahasiswa Aktif')
                                    ->state(function () {
                                        return MahasiswaKelas::query()
                                            ->where('kelas_id', $this->getOwnerRecord()->id)
                                            ->whereNull('tanggal_keluar')
                                            ->count();
                                    }),

                                TextEntry::make('sisa_kapasitas')
                                    ->label('Sisa Kapasitas')
                                    ->state(function () {
                                        $kelas = $this->getOwnerRecord();

                                        if ($kelas->kapasitas === null) {
                                            return 'Tidak dibatasi';
                                        }

                                        $jumlahAktif = MahasiswaKelas::query()
                                            ->where('kelas_id', $kelas->id)
                                            ->whereNull('tanggal_keluar')
                                            ->count();

                                        return max(0, $kelas->kapasitas - $jumlahAktif);
                                    })
                                    ->badge(),
                            ])
                            ->columns(2),

                        /*
        |--------------------------------------------------------------------------
        | MAHASISWA
        |--------------------------------------------------------------------------
        */

                        Section::make('Mahasiswa')
                            ->icon('heroicon-o-users')
                            ->description(
                                'Pilih satu atau beberapa mahasiswa. Angkatan mahasiswa tidak harus sama '
                                    . 'dengan angkatan kelas karena sistem mendukung restart/cuti dan penempatan lintas angkatan.'
                            )
                            ->schema([

                                Select::make('mahasiswa_ids')
                                    ->label('Mahasiswa')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(false)
                                    ->required()
                                    ->native(false)
                                    ->placeholder('Cari NIM atau nama mahasiswa...')
                                    ->helperText(
                                        'Mahasiswa yang masih aktif di kelas lain tidak akan ditampilkan.'
                                    )
                                    ->getSearchResultsUsing(function (string $search) {

                                        return Mahasiswa::query()
                                            ->whereDoesntHave(
                                                'mahasiswaKelas',
                                                fn($query) => $query
                                                    ->whereNull('tanggal_keluar')
                                            )
                                            ->where(function ($query) use ($search) {

                                                $query
                                                    ->where('nim', 'like', "%{$search}%")
                                                    ->orWhereHas(
                                                        'person',
                                                        fn($person) => $person
                                                            ->where(
                                                                'nama_lengkap',
                                                                'like',
                                                                "%{$search}%"
                                                            )
                                                    );
                                            })
                                            ->with('person')
                                            ->orderBy('nim')
                                            ->limit(30)
                                            ->get()
                                            ->mapWithKeys(function (Mahasiswa $mahasiswa) {

                                                $nama = $mahasiswa->person?->nama_lengkap ?? '-';

                                                return [
                                                    $mahasiswa->id =>
                                                    "{$mahasiswa->nim} — {$nama} "
                                                        . "[Angkatan {$mahasiswa->angkatan_id}]",
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelsUsing(
                                        fn(array $values): array =>
                                        Mahasiswa::query()
                                            ->whereIn('id', $values)
                                            ->with('person')
                                            ->get()
                                            ->mapWithKeys(function (Mahasiswa $mahasiswa) {

                                                $nama = $mahasiswa->person?->nama_lengkap ?? '-';

                                                return [
                                                    $mahasiswa->id =>
                                                    "{$mahasiswa->nim} — {$nama} "
                                                        . "[Angkatan {$mahasiswa->angkatan_id}]",
                                                ];
                                            })
                                            ->toArray()
                                    )
                                    ->live()
                                    ->columnSpanFull(),

                                /*
                |--------------------------------------------------------------------------
                | INFORMASI MAHASISWA TERPILIH
                |--------------------------------------------------------------------------
                */

                                TextEntry::make('mahasiswa_terpilih')
                                    ->label('Ringkasan Mahasiswa Terpilih')
                                    ->state(function (Get $get) {

                                        $ids = $get('mahasiswa_ids');

                                        if (blank($ids)) {
                                            return 'Belum ada mahasiswa yang dipilih.';
                                        }

                                        $mahasiswa = Mahasiswa::query()
                                            ->whereIn('id', $ids)
                                            ->with('person')
                                            ->get();

                                        return $mahasiswa
                                            ->map(function (Mahasiswa $m) {

                                                $nama = $m->person?->nama_lengkap ?? '-';

                                                return "{$m->nim} — {$nama} "
                                                    . "(Angkatan {$m->angkatan_id})";
                                            })
                                            ->implode("\n");
                                    })
                                    ->visible(fn(Get $get) => filled($get('mahasiswa_ids')))
                                    ->columnSpanFull(),

                            ])
                            ->columns(2),

                        /*
        |--------------------------------------------------------------------------
        | WARNING LINTAS ANGKATAN
        |--------------------------------------------------------------------------
        */

                        TextEntry::make('peringatan_lintas_angkatan')
                            ->label('Perhatian')
                            ->state(function (Get $get) {

                                $ids = $get('mahasiswa_ids');

                                if (blank($ids)) {
                                    return null;
                                }

                                $kelas = $this->getOwnerRecord();

                                $mahasiswa = Mahasiswa::query()
                                    ->whereIn('id', $ids)
                                    ->get();

                                $lintasAngkatan = $mahasiswa
                                    ->filter(
                                        fn(Mahasiswa $m) =>
                                        (int) $m->angkatan_id !== (int) $kelas->angkatan_id
                                    );

                                if ($lintasAngkatan->isEmpty()) {
                                    return null;
                                }

                                return $lintasAngkatan
                                    ->map(
                                        fn(Mahasiswa $m) =>
                                        "{$m->nim} — Angkatan {$m->angkatan_id} "
                                            . "→ Kelas Angkatan {$kelas->angkatan_id}"
                                    )
                                    ->implode("\n");
                            })
                            ->visible(function (Get $get) {

                                $ids = $get('mahasiswa_ids');

                                if (blank($ids)) {
                                    return false;
                                }

                                $kelas = $this->getOwnerRecord();

                                return Mahasiswa::query()
                                    ->whereIn('id', $ids)
                                    ->where('angkatan_id', '!=', $kelas->angkatan_id)
                                    ->exists();
                            })
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->columnSpanFull(),

                        /*
        |--------------------------------------------------------------------------
        | TANGGAL PENEMPATAN
        |--------------------------------------------------------------------------
        */

                        Section::make('Periode Penempatan')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([

                                DatePicker::make('tanggal_masuk')
                                    ->label('Tanggal Masuk Kelas')
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d F Y')
                                    ->required()
                                    ->maxDate(now())
                                    ->helperText(
                                        'Tanggal mulai mahasiswa resmi ditempatkan pada kelas ini.'
                                    ),

                            ])
                            ->columns(1),
                    ])
                    ->action(function (
                        array $data,
                        MahasiswaPlottingService $service
                    ) {

                        $sukses = 0;
                        $gagal = 0;
                        $errorLog = [];

                        foreach ($data['mahasiswa_ids'] as $mahasiswaId) {

                            try {

                                $service->plot(
                                    $mahasiswaId,
                                    $this->getOwnerRecord()->id,
                                    $data['tanggal_masuk']
                                );

                                $sukses++;
                            } catch (\Throwable $e) {

                                $gagal++;

                                Log::error(
                                    'Plotting Mahasiswa Error',
                                    [
                                        'mahasiswa_id' => $mahasiswaId,
                                        'kelas_id' => $this->getOwnerRecord()->id,
                                        'error' => $e->getMessage(),
                                    ]
                                );

                                $errorLog[] = $e->getMessage();
                            }
                        }

                        if ($gagal === 0) {

                            Notification::make()
                                ->title('Plotting Berhasil')
                                ->body(
                                    "{$sukses} mahasiswa berhasil ditempatkan "
                                        . "ke kelas {$this->getOwnerRecord()->nama_kelas}."
                                )
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Plotting Selesai dengan Catatan')
                            ->body(
                                "Berhasil: {$sukses}. "
                                    . "Gagal: {$gagal}. "
                                    . implode(' | ', array_unique($errorLog))
                            )
                            ->warning()
                            ->persistent()
                            ->send();
                    })
            ])
            ->recordActions([
                Action::make('mutasi')
                    ->icon('heroicon-o-arrows-right-left')->color('warning')
                    ->visible(fn($record) => $record->tanggal_keluar === null)
                    ->schema([
                        Select::make('target_kelas_id')
                            ->label('Kelas Tujuan')
                            ->options(fn() => \App\Models\Kelas::query()
                                ->where('id', '!=', $this->getOwnerRecord()->id)
                                ->where('prodi_id', $this->getOwnerRecord()->prodi_id)
                                ->where('program_id', $this->getOwnerRecord()->program_id)
                                ->orderBy('angkatan_id')
                                ->orderBy('nama_kelas')
                                ->get()
                                ->mapWithKeys(fn($kelas) => [
                                    $kelas->id => "{$kelas->nama_kelas} — Angkatan {$kelas->angkatan_id}",
                                ])
                                ->toArray())
                            ->searchable()
                            ->required(),
                        DatePicker::make('tanggal_mutasi')->default(now())->required(),
                    ])
                    ->action(fn($record, array $data, MahasiswaMutasiService $srv) =>
                    $srv->pindahKelas($record, $data['target_kelas_id'], $data['tanggal_mutasi'])),

                ActionsDeleteAction::make('hapus_plotting')
                    ->before(function ($record, MahasiswaPlottingService $srv, $action) {
                        if (!$srv->canDelete($record)) {
                            Notification::make()->danger()->title('Gagal Hapus')->body('Data memiliki histori akademik!')->send();
                            $action->cancel(); // Stop proses delete
                        }
                    })
            ]);
    }
}
