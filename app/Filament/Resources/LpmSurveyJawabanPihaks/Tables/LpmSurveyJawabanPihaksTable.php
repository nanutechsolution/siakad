<?php

namespace App\Filament\Resources\LpmSurveyJawabanPihaks\Tables;

use App\Models\LpmSurveyJawabanPihak;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmSurveyJawabanPihaksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis_responden')->label('Jenis Responden')->badge(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->state(fn(LpmSurveyJawabanPihak $record) => $record->namaTampilan()),
                TextColumn::make('pertanyaan.bunyi_pertanyaan')->label('Pertanyaan')->limit(50)->wrap(),
                TextColumn::make('jawaban_nilai')->label('Jawaban')->limit(30),
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
