<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\RelationManagers;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    protected static ?string $relatedResource = JadwalGeneratorBatchResource::class;
    protected static ?string $title = 'Preview Jadwal (Sandbox)';
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_success')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('mataKuliah.nama_mk')->label('Mata Kuliah')->searchable(),
                TextColumn::make('kelas.nama_kelas')->label('Kelas')->searchable(),

                TextColumn::make('hari')
                    ->label('Jadwal & Ruang')
                    ->formatStateUsing(function ($record) {
                        if (!$record->is_success) return '-';
                        return "{$record->hari}, {$record->jam_mulai} - {$record->jam_selesai} | Ruang: " . ($record->ruang->nama_ruang ?? '?');
                    }),

                TextColumn::make('failure_reason')
                    ->label('Keterangan / Alasan Gagal')
                    ->color('danger')
                    ->copyable()
                    ->wrap(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('Gagal')
                    ->query(fn($query) => $query->where('is_success', false)),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
