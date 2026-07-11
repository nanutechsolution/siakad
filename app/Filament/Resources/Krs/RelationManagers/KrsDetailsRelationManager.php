<?php

namespace App\Filament\Resources\Krs\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KrsDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'krsDetails';
    protected static ?string $title = 'Daftar Mata Kuliah Diambil';
    protected static ?string $modelLabel = 'Mata Kuliah';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jadwal_kuliah_id')
                    ->label('Pilih Jadwal Baru')
                    ->relationship('jadwalKuliah', 'id', function (Builder $query, RelationManager $livewire) {
                        $krs = $livewire->getOwnerRecord();
                        // Hanya tampilkan jadwal pada TA yang sama
                        $query->where('tahun_akademik_id', $krs->tahun_akademik_id)
                            ->with(['mataKuliah', 'kelas']);
                    })
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->mataKuliah->nama_mk} - Kelas {$record->kelas->nama_kelas}")
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_mk_snapshot')
            ->columns([
                TextColumn::make('kode_mk_snapshot')
                    ->label('Kode MK')
                    ->sortable(),
                TextColumn::make('nama_mk_snapshot')
                    ->label('Mata Kuliah')
                    ->sortable(),
                TextColumn::make('sks_snapshot')
                    ->label('SKS')
                    ->numeric(),
                TextColumn::make('status_ambil')
                    ->label('Status')
                    ->badge(),
                ViewColumn::make('jadwalKuliah.kapasitas')
                    ->label('Kapasitas Kelas')
                    // Membuat custom view inline untuk progress bar kapasitas kelas
                    ->view('filament.tables.columns.progress-bar-kapasitas'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data, RelationManager $livewire): array {
                        // Sinkronisasi snapshot saat tambah record dari relation manager
                        $jadwal = \App\Models\JadwalKuliah::with('mataKuliah')->find($data['jadwal_kuliah_id']);
                        if ($jadwal) {
                            $data['mata_kuliah_id'] = $jadwal->mata_kuliah_id;
                            $data['kode_mk_snapshot'] = $jadwal->mataKuliah->kode_mk;
                            $data['nama_mk_snapshot'] = $jadwal->mataKuliah->nama_mk;
                            $data['sks_snapshot'] = $jadwal->mataKuliah->sks_default;
                            $data['status_ambil'] = 'B';
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
