<?php

namespace App\Filament\Resources\SinkronisasiBatches\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SinkronisasiBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Batch')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('mode')->badge(),
                            TextEntry::make('status')->badge()
                                ->color(fn(string $state) => match ($state) {
                                    'COMPLETED' => 'success',
                                    'FAILED' => 'danger',
                                    default => 'warning',
                                }),
                            TextEntry::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                            TextEntry::make('createdBy.name')->label('Dijalankan Oleh'),
                            TextEntry::make('total_mahasiswa')->label('Mhs Diperiksa'),
                            TextEntry::make('total_ditambah')->label('Ditambah')->color('success'),
                            TextEntry::make('total_review')->label('Masuk Review')->color('warning'),
                            TextEntry::make('total_warning')->label('Warning')->color('danger'),
                        ]),
                        TextEntry::make('error_message')
                            ->label('Pesan Error')
                            ->visible(fn($record) => filled($record->error_message))
                            ->color('danger'),
                    ]),

                Section::make('Parameter yang Dijalankan (Audit Reproducibility)')
                    ->description('Snapshot persis parameter form saat batch ini dibuat - dipertahankan apa adanya walau data master (prodi/angkatan) berubah di kemudian hari.')
                    ->collapsible()
                    ->schema([
                        KeyValueEntry::make('parameter_snapshot')->hiddenLabel(),
                    ]),

                Section::make('Komponen yang Di-warning (Tidak Ada di Skema Aktif)')
                    ->visible(fn($record) => ! empty($record->summary_snapshot['komponen_warning'] ?? []))
                    ->collapsible()
                    ->schema([
                        KeyValueEntry::make('summary_snapshot.komponen_warning')
                            ->hiddenLabel()
                            ->keyLabel('Nama Komponen')
                            ->valueLabel('Jumlah Mahasiswa Terdampak'),
                    ]),
            ]);
    }
}
