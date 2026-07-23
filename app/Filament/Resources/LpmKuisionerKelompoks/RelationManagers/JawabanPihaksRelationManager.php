<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JawabanPihaksRelationManager extends RelationManager
{
    protected static string $relationship = 'jawabanPihaks';
    protected static ?string $title = 'Jawaban Dosen / Tendik / Alumni / Pengguna Lulusan';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('jenis_responden')->label('Jenis Responden')->badge(),
                TextColumn::make('person.nama_lengkap')
                    ->label('Nama')
                    ->formatStateUsing(fn($record) => $record->namaTampilan()),
                TextColumn::make('instansi_eksternal')->label('Instansi')->toggleable(),
                TextColumn::make('pertanyaan.bunyi_pertanyaan')->label('Pertanyaan')->wrap()->limit(60),
                TextColumn::make('jawaban_nilai')->label('Jawaban')->limit(40),
                TextColumn::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
            ])
            ->filters([
                SelectFilter::make('jenis_responden')
                    ->options([
                        'DOSEN' => 'Dosen',
                        'TENDIK' => 'Tenaga Kependidikan',
                        'ALUMNI' => 'Alumni',
                        'PENGGUNA_LULUSAN' => 'Pengguna Lulusan',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
