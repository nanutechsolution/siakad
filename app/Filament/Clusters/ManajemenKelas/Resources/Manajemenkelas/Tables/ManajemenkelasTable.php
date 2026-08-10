<?php

namespace App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Kelas;
use App\Models\MahasiswaKelas;
use App\Models\RefAngkatan;
use App\Services\Kelas\ManajemenKelasService;
use App\Support\Utf8;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ManajemenkelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->formatStateUsing(fn(?string $state) => $state ? Utf8::clean($state) : null)
                    ->searchable(),
                TextColumn::make('program.nama_program')
                    ->label('Program')
                    ->formatStateUsing(fn(?string $state) => $state ? Utf8::clean($state) : null)
                    ->searchable(),
                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable(),
                TextColumn::make('anggota')
                    ->label('Anggota Aktif')
                    ->getStateUsing(function (Kelas $record) {
                        $service = app(ManajemenKelasService::class);
                        $jumlah = $service->jumlahAnggotaAktif($record->id);
                        $sisa = $service->kapasitasTersisa($record);

                        if ($record->kapasitas === null) {
                            return "{$jumlah} (tanpa batas)";
                        }

                        return "{$jumlah} / {$record->kapasitas}" . ($sisa === 0 ? ' (PENUH)' : '');
                    })
                    ->badge()
                    ->color(function (Kelas $record) {
                        if ($record->kapasitas === null) {
                            return 'gray';
                        }

                        $sisa = app(ManajemenKelasService::class)->kapasitasTersisa($record);

                        return $sisa === 0 ? 'danger' : ($sisa <= 3 ? 'warning' : 'success');
                    }),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(function (Kelas $record) {
                        // FK mahasiswa_kelas.kelas_id itu ON DELETE CASCADE —
                        // kalau kelas dihapus, SELURUH histori keanggotaan
                        // (bukan cuma yang aktif) ikut terhapus permanen.
                        // Jadi ini benar-benar diblokir, bukan sekadar
                        // dikonfirmasi, kalau kelas pernah punya anggota.
                        return MahasiswaKelas::query()->where('kelas_id', $record->id)->exists();
                    })
                    ->tooltip(function (Kelas $record) {
                        $pernahPunya = MahasiswaKelas::query()->where('kelas_id', $record->id)->exists();

                        return $pernahPunya
                            ? 'Tidak bisa dihapus — kelas ini pernah/sedang punya anggota. Hapus riwayat keanggotaan tidak didukung demi menjaga histori akademik.'
                            : null;
                    })
                    ->requiresConfirmation()
                    ->modalDescription('Kelas ini belum pernah punya anggota — aman dihapus.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('hapusMassal')
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Kelas yang pernah/sedang punya anggota otomatis dilewati (tidak dihapus) demi menjaga histori akademik — hanya kelas kosong yang benar-benar terhapus.')
                        ->action(function (Collection $records): void {
                            $dihapus = 0;
                            $dilewati = 0;

                            foreach ($records as $record) {
                                $pernahPunyaAnggota = MahasiswaKelas::query()->where('kelas_id', $record->id)->exists();

                                if ($pernahPunyaAnggota) {
                                    $dilewati++;

                                    continue;
                                }

                                $record->delete();
                                $dihapus++;
                            }

                            Notification::make()
                                ->title('Hapus massal selesai')
                                ->body("{$dihapus} kelas terhapus, {$dilewati} dilewati karena pernah punya anggota.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Belum ada kelas')
            ->emptyStateDescription('Buat kelas manual di sini, atau pakai menu "Generate Kelas Otomatis" untuk membuat banyak kelas sekaligus per angkatan.')
            ->emptyStateIcon('heroicon-o-squares-2x2')
            ->emptyStateActions([
                CreateAction::make()->slideOver(),
            ])
            ->defaultSort('angkatan_id', 'desc');
    }
}
