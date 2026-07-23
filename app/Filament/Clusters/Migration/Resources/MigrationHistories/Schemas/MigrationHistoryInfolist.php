<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories\Schemas;

use App\Models\MigrationBatch;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MigrationHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Batch')
                ->columns(3)
                ->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('source')->label('Sumber')
                        ->formatStateUsing(fn($state) => $state->label()),
                    TextEntry::make('status')->label('Status')
                        ->badge()
                        ->color(fn($state) => $state->color())
                        ->formatStateUsing(fn($state) => $state->label()),
                    TextEntry::make('file_name')->label('Nama File'),
                    TextEntry::make('total_rows')->label('Total Baris'),
                    TextEntry::make('total_berhasil')->label('Berhasil'),
                    TextEntry::make('total_gagal')->label('Gagal'),
                    TextEntry::make('total_dilewati')->label('Dilewati'),
                    TextEntry::make('execution_time_seconds')->label('Waktu Proses (detik)'),
                    TextEntry::make('creator.name')->label('Operator'),
                    TextEntry::make('started_at')->label('Mulai')->dateTime('d M Y H:i'),
                    TextEntry::make('completed_at')->label('Selesai')->dateTime('d M Y H:i'),
                ]),
            Section::make('Detail Log (200 Baris Terbaru)')
                ->schema([
                    RepeatableEntry::make('logs')
                        ->label('')
                        ->state(fn(MigrationBatch $record) => $record->logs()->latest('row_number')->limit(200)->get())
                        ->schema([
                            TextEntry::make('row_number')->label('Baris'),
                            TextEntry::make('nim')->label('NIM'),
                            TextEntry::make('status')->label('Status')
                                ->badge()
                                ->color(fn($state) => $state->color())
                                ->formatStateUsing(fn($state) => $state->label()),
                            TextEntry::make('pesan')->label('Pesan')->columnSpanFull(),
                        ])
                        ->columns(3),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
