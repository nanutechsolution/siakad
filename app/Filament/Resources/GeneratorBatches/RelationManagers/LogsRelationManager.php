<?php

namespace App\Filament\Resources\GeneratorBatches\RelationManagers;

use App\Filament\Resources\GeneratorBatches\GeneratorBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = 'Log Per Mahasiswa';
    protected static ?string $relatedResource = GeneratorBatchResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('mahasiswa.nim')->label('NIM'),
                TextColumn::make('mahasiswa.person.nama_lengkap')->label('Nama'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'BERHASIL' => 'success',
                        'GAGAL' => 'danger',
                        'DILEWATI' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('total_tagihan')->label('Total Tagihan')->money('IDR')->placeholder('-'),
                TextColumn::make('pesan')->label('Catatan')->wrap()->limit(80),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'BERHASIL' => 'Berhasil',
                    'GAGAL' => 'Gagal',
                    'DILEWATI' => 'Dilewati',
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }
    public  function canCreate(): bool
    {
        return false;
    }

    public  function canEdit($record): bool
    {
        return false;
    }

    public  function canDelete($record): bool
    {
        return false;
    }
}
