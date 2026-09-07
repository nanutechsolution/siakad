<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;

class JadwalGeneratorBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Eksekusi')
                    ->schema([
                        TextEntry::make('tahunAkademik.nama_tahun')
                            ->label('Tahun Akademik'),
                        
                        TextEntry::make('prodi.nama_prodi')
                            ->label('Program Studi'),
                            
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'RUNNING' => 'warning',
                                'PREVIEW' => 'info',
                                'COMMITTED' => 'success',
                                'FAILED' => 'danger',
                                default => 'gray',
                            }),
                            
                        TextEntry::make('created_at')
                            ->label('Waktu Generate')
                            ->dateTime('d M Y, H:i'),
                    ])->columns(4),

                Section::make('Statistik Hasil Penjadwalan')
                    ->schema([
                        TextEntry::make('total_generated')
                            ->label('Berhasil Dijadwalkan')
                            ->badge()
                            ->color('success'),
                            
                        TextEntry::make('total_failed')
                            ->label('Gagal Plotting (Bentrok/Ruang Penuh)')
                            ->badge()
                            ->color('danger'),
                    ])->columns(2),

                Section::make('Konfigurasi Parameter Mesin (Snapshot)')
                    ->description('Pengaturan jam dan hari yang digunakan mesin saat jadwal ini di-generate.')
                    ->schema([
                        TextEntry::make('config_snapshot.hari')
                            ->label('Hari Operasional')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
                            
                        RepeatableEntry::make('config_snapshot.slots')
                            ->label('Blok Waktu Aktif')
                            ->schema([
                                TextEntry::make('mulai')
                                    ->label('Mulai'),
                                TextEntry::make('selesai')
                                    ->label('Selesai'),
                            ])
                            ->columns(2)
                            ->grid(3), // Menampilkan slot ke dalam bentuk grid agar ringkas
                    ])->columns(1),
            ]);
    }
}