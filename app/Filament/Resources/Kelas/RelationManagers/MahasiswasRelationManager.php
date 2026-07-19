<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use App\Services\MahasiswaPlottingService;
use App\Services\MahasiswaMutasiService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Illuminate\Support\Facades\Log;

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
                                $kelas = $this->getOwnerRecord();
                                return Mahasiswa::query()
                                    ->where('angkatan_id', $kelas->angkatan_id) // Filter Angkatan
                                    ->whereDoesntHave('mahasiswaKelas', fn($q) => $q->whereNull('tanggal_keluar'))
                                    ->where(fn($q) => $q->whereHas('person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%"))
                                        ->orWhere('nim', 'like', "%{$search}%"))
                                    ->limit(30)
                                    ->pluck('nim', 'id')
                                    ->map(fn($nim, $id) => $nim . ' - ' . Mahasiswa::find($id)->person->nama_lengkap);
                            })
                            ->getOptionLabelsUsing(fn(array $values): array =>
                            Mahasiswa::whereIn('id', $values)->with('person')->get()
                                ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person->nama_lengkap}"])->toArray()),
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
                    ->form([
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
                    $srv->mutasi($record, $data['target_kelas_id'], $data['tanggal_mutasi'])),

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
