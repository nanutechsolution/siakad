<?php

namespace App\Filament\Resources\Khs\RelationManagers;

use App\Filament\Resources\Khs\KhsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KrsDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'krs_details';

    protected static ?string $relatedResource = KhsResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('mata_kuliah_id')
            ->heading('Daftar Mata Kuliah dan Nilai')
            ->description('Rincian perolehan nilai mata kuliah yang diambil pada semester ini.')
            ->columns([
                TextColumn::make('mataKuliah.kode_mk')
                    ->label('Kode')
                    ->searchable(),

                TextColumn::make('nama_mk_snapshot')
                    ->label('Mata Kuliah')
                    ->weight('bold') // Ditebalkan agar mudah dibaca
                    ->searchable(),

                TextColumn::make('sks_snapshot')
                    ->label('SKS')
                    ->numeric()
                    ->summarize(Sum::make()->label('Total SKS')), // UX: Otomatis menjumlahkan SKS di bawah tabel

                TextColumn::make('nilai_angka')
                    ->label('Angka')
                    ->alignCenter(),

                TextColumn::make('nilai_huruf')
                    ->label('Huruf')
                    ->alignCenter()
                    ->badge() // UX: Warna berbeda untuk setiap grade nilai
                    ->color(fn(string $state): string => match ($state) {
                        'A', 'A-' => 'success',
                        'B+', 'B', 'B-' => 'info',
                        'C+', 'C' => 'warning',
                        'D', 'E' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('nilai_indeks')
                    ->label('Indeks (Bobot)')
                    ->numeric(2) // Format 2 angka di belakang koma (misal: 3.50)
                    ->alignCenter(),
            ])
            ->striped() // UX: Baris zebra agar mata tidak lelah membaca tabel panjang
            ->paginated(false); // KHS biasanya ditampilkan dalam 1 halaman utuh, tidak perlu pagination
    }
}
