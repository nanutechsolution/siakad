<?php

namespace App\Filament\Resources\SinkronisasiBatches\RelationManagers;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

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
                TextColumn::make('jumlah_ditambah')->label('Ditambah')->numeric(),
                TextColumn::make('jumlah_review')->label('Review')->numeric(),
                TextColumn::make('jumlah_warning')->label('Warning')->numeric(),
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
