<?php

namespace App\Filament\Resources\Kelas\RelationManagers;

use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MahasiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'mahasiswas';
    protected static ?string $title = 'Anggota Kelas';
    protected static ?string $modelLabel = 'Mahasiswa';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('mahasiswa_id')
                    ->label('Pilih Mahasiswa Aktif')
                    ->options(
                        \App\Services\MahasiswaAkademikService::getMahasiswaAktifQuery()
                            ->with('person') // Eager load relasi person agar tidak terjadi N+1
                            ->get()
                            ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person->nama_lengkap}"])
                    )
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search) =>
                        \App\Services\MahasiswaAkademikService::getMahasiswaAktifQuery()
                            ->whereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                            ->orWhere('nim', 'like', "%{$search}%")
                            ->limit(50)
                            ->with('person')
                            ->get()
                            ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person->nama_lengkap}"])
                    )
                    ->required(),

                DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk Kelas')
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nim')
            ->columns([
                TextColumn::make('nim')->label('NIM')->sortable(),
                TextColumn::make('person.nama_lengkap')->label('Nama Mahasiswa')->searchable(),
                TextColumn::make('pivot.tanggal_masuk')->label('Masuk Kelas')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('plot_mahasiswa')
                    ->label('Plotting Mahasiswa')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->schema([
                        Select::make('mahasiswa_id')
                            ->label('Pilih Mahasiswa')
                            ->options(
                                Mahasiswa::query()
                                    ->join('ref_person', 'mahasiswas.person_id', '=', 'ref_person.id')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person->nama_lengkap}"])
                            )
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                Mahasiswa::query()
                                    ->join('ref_person', 'mahasiswas.person_id', '=', 'ref_person.id')
                                    ->where('nim', 'like', "%{$search}%")
                                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - {$m->person->nama_lengkap}"])
                            )
                            ->required(),
                        DatePicker::make('tanggal_masuk')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire): void {
                        // Gunakan ownerRecord untuk mendapatkan ID Kelas yang sedang dibuka
                        Mahasiswa::query()
                            ->join('ref_person', 'mahasiswas.person_id', '=', 'ref_person.id')
                            ->select('mahasiswas.id', 'mahasiswas.nim', 'ref_person.nama_lengkap') // Pastikan pilih id mahasiswa
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn($m) => [(int) $m->id => "{$m->nim} - {$m->nama_lengkap}"]);

                        \Filament\Notifications\Notification::make()
                            ->title('Mahasiswa Berhasil Diplot')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make()->label("Hapus Dari Kelas Ini"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
