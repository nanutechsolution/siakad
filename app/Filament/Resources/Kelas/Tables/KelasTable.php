<?php

namespace App\Filament\Resources\Kelas\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Kelas;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
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
            // PERBAIKAN: Eager load relasi prodi dan program untuk menghabisi N+1 query pada list table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['prodi', 'program']))
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

                TextColumn::make('mahasiswa_kelas_aktif_count')
                    ->label('Isi Kelas')
                    ->counts('mahasiswaKelasAktif')
                    ->badge()
                    ->color(function (int $state, Kelas $record): string {
                        return $state >= $record->kapasitas ? 'danger' : 'success';
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
                                // Cek apakah kelas ini masih memiliki mahasiswa aktif ATAU dosen wali terikat
                                // Pastikan di model Kelas Anda sudah ada relasi 'dosenWali' atau sesuaikan namanya
                                $hasMahasiswaAktif = $record->mahasiswaKelasAktif()->exists();
                                $hasDosenWali = method_exists($record, 'dosenWali') ? $record->dosenWali()->exists() : false;

                                if ($hasMahasiswaAktif || $hasDosenWali) {
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
                                    ->body("Ada {$gagalHapus} kelas yang tidak bisa dihapus karena masih memiliki mahasiswa aktif atau dosen wali terikat.")
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
