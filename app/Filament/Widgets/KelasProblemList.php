<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Services\Kelas\KelasDashboardService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class KelasProblemList extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return 'Kelas Perlu Perhatian';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(app(KelasDashboardService::class)->problemQuery())
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prodi.nama_prodi')
                    ->label('Prodi'),

                Tables\Columns\TextColumn::make('program.nama_program')
                    ->label('Program'),

                Tables\Columns\TextColumn::make('angkatan.nama')
                    ->label('Angkatan'),

                Tables\Columns\TextColumn::make('mahasiswa_aktif_count')
                    ->label('Mhs Aktif')
                    ->badge()
                    ->color(fn(Kelas $record) => $record->kapasitas !== null
                        && $record->mahasiswa_aktif_count > $record->kapasitas
                        ? 'danger'
                        : 'gray'),

                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->placeholder('— Tidak diset —'),
            ])
            ->paginated([5, 10, 25]);
    }
}
