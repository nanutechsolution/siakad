<?php

namespace App\Filament\Resources\Pegawais\Tables;

use App\Enums\HR\JenisPegawai;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('person.nama_dengan_gelar')
                    ->label('Nama Pegawai')
                    ->formatStateUsing(fn($record) => $record->person?->nama_dengan_gelar ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('person', function ($q) use ($search) {
                            $q->where('nama_lengkap', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('person.jenis_kelamin')
                    ->label('L/P')
                    ->sortable(),
                TextColumn::make('jenis_pegawai')
                    ->label('Status Pegawai')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tgl Registrasi')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis_pegawai')
                    ->options(JenisPegawai::class)
                    ->label('Filter Status Pegawai'),
                TernaryFilter::make('is_active')
                    ->label('Filter Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
