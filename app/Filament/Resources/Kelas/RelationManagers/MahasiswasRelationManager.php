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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                    ->modalWidth('xl')
                    ->schema([
                        Select::make('mahasiswa_ids')
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->getSearchResultsUsing(function (string $search) {
                                return Mahasiswa::query()
                                    ->whereDoesntHave(
                                        'mahasiswaKelas',
                                        fn($q) => $q->whereNull('tanggal_keluar')
                                    )
                                    ->where(function ($q) use ($search) {
                                        $q->whereHas(
                                            'person',
                                            fn($p) =>
                                            $p->where(
                                                'nama_lengkap',
                                                'like',
                                                "%{$search}%"
                                            )
                                        )
                                            ->orWhere('nim', 'like', "%{$search}%");
                                    })
                                    ->with('person')
                                    ->limit(30)
                                    ->get()
                                    ->mapWithKeys(fn($m) => [
                                        $m->id =>
                                        "{$m->nim} - {$m->person->nama_lengkap} [Angkatan {$m->angkatan_id}]",
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelsUsing(
                                fn(array $values): array =>
                                Mahasiswa::whereIn('id', $values)
                                    ->with('person')
                                    ->get()
                                    ->mapWithKeys(fn($m) => [
                                        $m->id => "{$m->nim} - {$m->person->nama_lengkap} [Angkatan {$m->angkatan_id}]",
                                    ])
                                    ->toArray()
                            ),
                        DatePicker::make('tanggal_masuk')->default(now())->required(),
                    ])
                    ->action(function (array $data, MahasiswaPlottingService $service) {
                        $sukses = 0;
                        $gagal = 0;
                        $errorLog = [];
                        foreach ($data['mahasiswa_ids'] as $id) {
                            try {
                                $service->plot($id, $this->getOwnerRecord()->id, $data['tanggal_masuk']);
                                $sukses++;
                            } catch (\Exception $e) {
                                $gagal++;
                                Log::error("Plotting Error: " . $e->getMessage());
                                $errorLog[] = $e->getMessage();
                            }
                        }
                        Notification::make()
                            ->title($gagal == 0 ? 'Berhasil' : 'Selesai dengan Catatan')
                            ->body("Sukses: $sukses, Gagal: $gagal. " . ($gagal > 0 ? "Pesan error: " . implode(', ', array_unique($errorLog)) : ""))
                            ->status($gagal == 0 ? 'success' : 'warning')
                            ->send();
                    }),
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
