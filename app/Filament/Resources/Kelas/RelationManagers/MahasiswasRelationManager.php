<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use App\Exports\Kelas\MahasiswaKelasImport as KelasMahasiswaKelasImport;
use App\Models\MahasiswaKelas;
use App\Services\MahasiswaMutasiService;
use App\Services\MahasiswaPlottingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'mahasiswaKelas';
    protected static ?string $title = 'Anggota Kelas';

    /**
     * RelationManager ini fokus mengelola mahasiswa yang SUDAH berada di kelas ini.
     * Plotting mahasiswa baru (tambah_mahasiswa) sudah dipindah ke header action
     * ViewKelas, dan export dipindah ke header action ViewKelas — supaya tidak
     * ada aksi yang muncul dobel antara halaman detail kelas dan relation manager ini.
     */
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
                // Hanya import_excel yang dipertahankan di sini: alur ini spesifik
                // untuk memasukkan data dari file eksternal ke kelas ini, berbeda
                // dari alur "tambah_mahasiswa" pilih-dari-daftar di ViewKelas.
                Action::make('import_excel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->schema([
                        FileUpload::make('file')
                            ->label('File Excel (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
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
                            $errorMessage = collect($failures)
                                ->map(fn($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                                ->implode('<br>');

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
            ])
            ->recordActions([
                // Aksi cepat per-baris: masih relevan karena mahasiswa & kelas asal
                // sudah diketahui dari $record, jadi operator cukup pilih kelas tujuan.
                Action::make('mutasi')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->visible(fn($record) => $record->tanggal_keluar === null)
                    ->schema([
                        Select::make('target_kelas_id')
                            ->options(fn() => \App\Models\Kelas::query()
                                ->where('id', '!=', $this->getOwnerRecord()->id)
                                ->where('prodi_id', $this->getOwnerRecord()->prodi_id)
                                ->where('angkatan_id', $this->getOwnerRecord()->angkatan_id)
                                ->pluck('nama_kelas', 'id'))
                            ->required(),
                        DatePicker::make('tanggal_mutasi')->default(now())->required(),
                    ])
                    ->action(fn($record, array $data, MahasiswaMutasiService $srv) =>
                    $srv->pindahKelas($record, $data['target_kelas_id'], $data['tanggal_mutasi'])),

                ActionsDeleteAction::make('hapus_plotting')
                    ->before(function ($record, MahasiswaPlottingService $srv, $action) {
                        if (!$srv->canDelete($record)) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Hapus')
                                ->body('Data memiliki histori akademik!')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }
}
