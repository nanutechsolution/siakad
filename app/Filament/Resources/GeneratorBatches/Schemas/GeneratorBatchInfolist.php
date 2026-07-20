<?php

namespace App\Filament\Resources\GeneratorBatches\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GeneratorBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Batch')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('status')->badge()
                                ->color(fn(string $state) => match ($state) {
                                    'COMPLETED' => 'success',
                                    'FAILED' => 'danger',
                                    default => 'warning',
                                }),
                            TextEntry::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                            TextEntry::make('createdBy.name')->label('Dijalankan Oleh'),
                            TextEntry::make('total_mahasiswa')->label('Mhs Diperiksa'),
                            TextEntry::make('total_berhasil')->label('Berhasil')->color('success'),
                            TextEntry::make('total_skip')->label('Sudah Ada Tagihan')->color('gray'),
                            TextEntry::make('total_gagal')->label('Gagal')->color('danger'),
                        ]),
                        TextEntry::make('error_message')
                            ->label('Pesan Error')
                            ->visible(fn($record) => filled($record->error_message))
                            ->color('danger'),
                    ]),

                Section::make('Parameter yang Dijalankan (Audit Reproducibility)')
                    ->description('Snapshot persis parameter form saat batch ini dibuat.')
                    ->collapsible()
                    ->schema([
                        KeyValueEntry::make('parameter_snapshot')->hiddenLabel(),
                    ]),
            ]);
    }
}
