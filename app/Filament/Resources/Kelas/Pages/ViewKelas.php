<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Exports\Kelas\MahasiswaKelasExport;
use App\Filament\Resources\Kelas\KelasResource;
use App\Filament\Resources\Kelas\Schemas\KelasInfolist;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use App\Services\MahasiswaMutasiService;
use App\Services\MahasiswaPlottingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ViewKelas extends ViewRecord
{
    protected static string $resource = KelasResource::class;

    public function infolist(Schema $schema): Schema
    {
        return KelasInfolist::configure($schema);
    }

    /**
     * Eager load count isi kelas sekali di sini, dipakai infolist + header actions,
     * supaya tidak query berulang saat render.
     */
    protected function resolveRecord($key): Model
    {
        return parent::resolveRecord($key)->loadCount('mahasiswaKelasAktif');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->tambahMahasiswaAction(),
            $this->mutasiMahasiswaAction(),
            $this->exportMahasiswaAction(),
        ];
    }

    protected function tambahMahasiswaAction(): Action
    {
        return Action::make('tambah_mahasiswa')
            ->label('Tambah Mahasiswa')
            ->icon('heroicon-o-user-plus')
            ->modalWidth('xl')
            ->schema([
                Select::make('mahasiswa_ids')
                    ->label('Mahasiswa')
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->getSearchResultsUsing(function (string $search) {
                        $kelas = $this->getRecord();

                        return Mahasiswa::query()
                            ->where('prodi_id', $kelas->prodi_id)
                            ->where('angkatan_id', $kelas->angkatan_id)
                            ->whereDoesntHave('mahasiswaKelas', fn($q) => $q->whereNull('tanggal_keluar'))
                            ->where(
                                fn($q) => $q
                                    ->whereHas('person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%"))
                                    ->orWhere('nim', 'like', "%{$search}%")
                            )
                            ->limit(30)
                            ->get()
                            ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person?->nama_lengkap}"]);
                    })
                    ->getOptionLabelsUsing(
                        fn(array $values) => Mahasiswa::whereIn('id', $values)->with('person')->get()
                            ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person?->nama_lengkap}"])
                            ->toArray()
                    ),

                DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (array $data, MahasiswaPlottingService $service) {
                $kelas = $this->getRecord();
                $sisaKapasitas = $kelas->kapasitas - $kelas->mahasiswa_kelas_aktif_count;
                $diminta = count($data['mahasiswa_ids']);

                if ($diminta > $sisaKapasitas) {
                    Notification::make()
                        ->title('Melebihi Kapasitas')
                        ->body("Sisa kapasitas kelas ini hanya {$sisaKapasitas}, dipilih {$diminta} mahasiswa.")
                        ->danger()
                        ->send();

                    return;
                }

                $sukses = 0;
                $gagal = 0;
                $errorLog = [];

                foreach ($data['mahasiswa_ids'] as $id) {
                    try {
                        $service->plot($id, $kelas->id, $data['tanggal_masuk']);
                        $sukses++;
                    } catch (\Exception $e) {
                        $gagal++;
                        Log::error('Tambah Mahasiswa (ViewKelas) Error: ' . $e->getMessage());
                        $errorLog[] = $e->getMessage();
                    }
                }

                $this->refreshFormData(['mahasiswa_kelas_aktif_count']);
                $this->record->refresh()->loadCount('mahasiswaKelasAktif');

                Notification::make()
                    ->title($gagal === 0 ? 'Berhasil' : 'Selesai dengan Catatan')
                    ->body(
                        "Sukses: {$sukses}, Gagal: {$gagal}."
                            . ($gagal > 0 ? ' Pesan error: ' . implode(', ', array_unique($errorLog)) : '')
                    )
                    ->status($gagal === 0 ? 'success' : 'warning')
                    ->send();
            });
    }

    protected function mutasiMahasiswaAction(): Action
    {
        return Action::make('mutasi_mahasiswa')
            ->label('Mutasi Mahasiswa')
            ->icon('heroicon-o-arrows-right-left')
            ->color('warning')
            ->modalWidth('xl')
            ->schema(function () {
                $kelas = $this->getRecord();

                return [
                    Select::make('mahasiswa_kelas_id')
                        ->label('Mahasiswa (di kelas ini)')
                        ->required()
                        ->searchable()
                        ->options(
                            fn() => $kelas->mahasiswaKelas()
                                ->whereNull('tanggal_keluar')
                                ->with('mahasiswa.person')
                                ->get()
                                ->mapWithKeys(fn($mk) => [
                                    $mk->id => "{$mk->mahasiswa?->nim} - {$mk->mahasiswa?->person?->nama_lengkap}",
                                ])
                        ),

                    Select::make('target_kelas_id')
                        ->label('Kelas Tujuan')
                        ->required()
                        ->searchable()
                        ->options(
                            fn() => Kelas::query()
                                ->where('id', '!=', $kelas->id)
                                ->where('prodi_id', $kelas->prodi_id)
                                ->where('angkatan_id', $kelas->angkatan_id)
                                ->pluck('nama_kelas', 'id')
                        ),

                    DatePicker::make('tanggal_mutasi')
                        ->default(now())
                        ->required(),
                ];
            })
            ->action(function (array $data, MahasiswaMutasiService $service) {
                $record = MahasiswaKelas::findOrFail($data['mahasiswa_kelas_id']);

                $service->pindahKelas($record, $data['target_kelas_id'], $data['tanggal_mutasi']);

                $this->record->refresh()->loadCount('mahasiswaKelasAktif');

                Notification::make()
                    ->title('Berhasil')
                    ->body('Mahasiswa dipindahkan ke kelas tujuan.')
                    ->success()
                    ->send();
            });
    }

    protected function exportMahasiswaAction(): Action
    {
        return Action::make('export_mahasiswa')
            ->label('Export Mahasiswa')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(fn() => Excel::download(
                new MahasiswaKelasExport($this->getRecord()),
                'kelas-' . $this->getRecord()->id . '.xlsx'
            ));
    }
}
