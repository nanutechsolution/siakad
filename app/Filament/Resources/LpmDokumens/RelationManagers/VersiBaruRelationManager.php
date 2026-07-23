<?php

namespace App\Filament\Resources\LpmDokumens\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VersiBaruRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayats';
    protected static ?string $title = 'Riwayat Perubahan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('versi_baru')
            ->columns([
                TextColumn::make('versi_lama')->label('Versi Lama'),
                TextColumn::make('versi_baru')->label('Versi Baru'),
                TextColumn::make('changelog')->label('Ringkasan Perubahan')->wrap(),
                TextColumn::make('diubahOleh.nama_lengkap')->label('Diubah Oleh'),
                TextColumn::make('tanggal')->label('Tanggal')->date('d/m/Y'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
