<?php

namespace App\Filament\Resources\Mahasiswas\Tables;

use App\Models\Kelas;
use App\Services\ManajemenKelasService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['person', 'prodi', 'angkatan', 'program']))
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('program.nama_program')
                    ->label('Kelas')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun')
                    ->searchable(),
                SelectFilter::make('program_id')
                    ->label('Program Kelas')
                    ->relationship('program', 'nama_program'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pindah_kelas')
                        ->label('Pindah Kelas (Auto-Exit)')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->form([
                            Select::make('kelas_tujuan_id')
                                ->label('Pilih Kelas Tujuan')
                                ->options(Kelas::query()->pluck('nama_kelas', 'id'))
                                ->required(),
                            DatePicker::make('tanggal_pindah')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $service = app(ManajemenKelasService::class);

                            foreach ($records as $record) {
                                $service->pindahKelas(
                                    $record->id,
                                    $data['kelas_tujuan_id'],
                                    $data['tanggal_pindah']
                                );
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil Dipindahkan')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
