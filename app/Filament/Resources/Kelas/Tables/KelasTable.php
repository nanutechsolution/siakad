<?php

namespace App\Filament\Resources\Kelas\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Kelas;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Actions\ViewAction;
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
                ViewAction::make()
                    ->label('Lihat'),
                ActionsEditAction::make(),
            ])
            ->toolbarActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make()
                        ->label('Hapus Kelas')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Kelas')
                        ->modalDescription('Hanya kelas yang belum pernah digunakan yang dapat dihapus.')
                        ->successNotification(null)
                        ->action(function (Collection $records) {

                            $berhasil = 0;
                            $gagal = [];

                            foreach ($records as $record) {

                                $alasan = [];

                                if ($record->mahasiswaKelas()->exists()) {
                                    $alasan[] = 'masih memiliki riwayat mahasiswa';
                                }

                                if ($record->pembimbingAkademik()->exists()) {
                                    $alasan[] = 'masih memiliki pembimbing akademik';
                                }

                                if (method_exists($record, 'jadwalKuliah') && $record->jadwalKuliah()->exists()) {
                                    $alasan[] = 'masih memiliki jadwal kuliah';
                                }

                                if (method_exists($record, 'krsDetail') && $record->krsDetail()->exists()) {
                                    $alasan[] = 'sudah digunakan pada KRS';
                                }

                                if (count($alasan)) {
                                    $gagal[] = [
                                        'nama'   => $record->nama_kelas,
                                        'alasan' => implode(', ', $alasan),
                                    ];

                                    continue;
                                }

                                $record->delete();
                                $berhasil++;
                            }

                            if ($berhasil > 0) {
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body("{$berhasil} kelas berhasil dihapus.")
                                    ->success()
                                    ->send();
                            }

                            if (count($gagal)) {

                                $body = collect($gagal)
                                    ->map(fn($item) => "• {$item['nama']} ({$item['alasan']})")
                                    ->implode("\n");

                                Notification::make()
                                    ->title(count($gagal) . ' kelas tidak dapat dihapus')
                                    ->body($body)
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
