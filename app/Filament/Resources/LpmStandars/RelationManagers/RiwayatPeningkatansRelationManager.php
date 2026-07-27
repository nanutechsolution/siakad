<?php

namespace App\Filament\Resources\LpmStandars\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiwayatPeningkatansRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatPeningkatans';
    protected static ?string $title = 'Riwayat Peningkatan';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('ringkasan_perubahan')
            ->columns([
                TextColumn::make('versi_lama')->label('Versi Lama'),
                TextColumn::make('versi_baru')->label('Versi Baru'),
                TextColumn::make('ringkasan_perubahan')->label('Ringkasan Perubahan')->wrap(),
                TextColumn::make('dasar_peningkatan')->label('Dasar Peningkatan')->badge(),
                TextColumn::make('disetujuiOleh.nama_lengkap')->label('Disetujui Oleh'),
                TextColumn::make('tanggal')->label('Tanggal')->date('d/m/Y'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
