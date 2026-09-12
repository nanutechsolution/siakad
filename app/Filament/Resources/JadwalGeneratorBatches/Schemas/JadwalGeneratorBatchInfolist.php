<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

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
                            ->color(fn(string $state): string => match ($state) {
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
                Section::make('Detail Error')
                    ->schema([
                        TextEntry::make('error_message')
                            ->label('Penyebab Gagal')
                            ->columnSpanFull()
                            ->copyable()
                            ->prose(),
                    ])
                    ->visible(fn($record) => $record?->status === 'FAILED')
                    ->columns(1),
            ]);
    }
}
