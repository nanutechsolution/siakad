<?php

namespace App\Filament\Resources\Kelas\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Kelas;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['prodi', 'program', 'kampus']))
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.nama_program')
                    ->label('Program')
                    ->badge()
                    ->color('info'),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('status_kampus')
                    ->label('Plot Kampus')
                    ->getStateUsing(function (Kelas $record): string {
                        return $record->kampus_id
                            ? ($record->kampus?->nama_kampus ?? 'Kampus tidak ditemukan')
                            : 'BELUM DI-PLOT';
                    })
                    ->badge()
                    ->color(fn(Kelas $record): string => $record->kampus_id ? 'success' : 'danger')
                    ->icon(fn(Kelas $record): string => $record->kampus_id
                        ? 'heroicon-o-building-office-2'
                        : 'heroicon-o-exclamation-triangle'),

                TextColumn::make('mahasiswa_kelas_aktif_count')
                    ->label('Isi Kelas')
                    ->counts('mahasiswaKelasAktif')
                    ->badge()
                    ->color(function (int $state, Kelas $record): string {
                        if ($record->kapasitas === null) {
                            return 'gray';
                        }

                        if ($state >= $record->kapasitas) {
                            return 'danger';
                        }

                        return ($record->kapasitas - $state) <= 3 ? 'warning' : 'success';
                    })
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user())),

                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => DB::table('ref_angkatan')->pluck('id_tahun', 'id_tahun')->toArray()),
                SelectFilter::make('kampus_id')
                    ->label('Kampus')
                    ->relationship('kampus', 'nama_kampus')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionsEditAction::make(),
            ])
            ->toolbarActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make()
                        // KUNCI: Matikan notifikasi sukses bawaan Filament agar tidak bentrok
                        ->successNotification(null)
                        ->action(function (Collection $records) {
                            $gagalHapus = 0;
                            $berhasilHapus = 0;

                            foreach ($records as $record) {
                                // Kelas pernah/sedang punya anggota -> jangan dihapus.
                                // Mahasiswa KRS juga menolak FK kelas (restrict), jadi
                                // amankan semuanya dalam satu cek: riwayat keanggotaan
                                // dan dosen wali/pembimbing terikat.
                                $adaRiwayat = $record->mahasiswaKelas()->exists();
                                $adaDosenWali = $record->pembimbingAkademik()->exists();

                                if ($adaRiwayat || $adaDosenWali) {
                                    $gagalHapus++;
                                    continue; // Lewati data ini, jangan di-delete
                                }

                                $record->delete();
                                $berhasilHapus++;
                            }

                            // Kondisi 1: Jika ada yang gagal dihapus
                            if ($gagalHapus > 0) {
                                Notification::make()
                                    ->title('Beberapa kelas gagal dihapus')
                                    ->body("Ada {$gagalHapus} kelas yang tidak bisa dihapus karena pernah/sedang punya anggota atau masih terikat dosen wali.")
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }

                            // Kondisi 2: Hanya muncul jika memang ada record yang benar-benar terhapus
                            if ($berhasilHapus > 0 && $gagalHapus === 0) {
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body("Sebanyak {$berhasilHapus} kelas berhasil dihapus.")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
