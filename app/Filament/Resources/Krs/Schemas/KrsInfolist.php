<?php

namespace App\Filament\Resources\Krs\Schemas;

use App\Enums\KrsStatusEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KrsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan KRS')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('mahasiswa.nim')->label('NIM')->weight('bold'),
                        TextEntry::make('mahasiswa.person.nama_lengkap')->label('Mahasiswa'),
                        TextEntry::make('mahasiswa.prodi.nama_prodi')->label('Program Studi'),
                        TextEntry::make('tahunAkademik.nama_tahun')->label('Periode'),
                        TextEntry::make('total_sks_diambil')->label('Total SKS')->badge(),
                        TextEntry::make('status_krs')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn(?KrsStatusEnum $state) => $state?->getLabel() ?? '-')
                            ->color(fn(?KrsStatusEnum $state) => $state?->getColor() ?? 'gray'),
                    ]),
                ]),
            Section::make('Verifikasi')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('is_financial_verified')
                            ->label('Status Keuangan')
                            ->badge()
                            ->formatStateUsing(fn(bool $state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                            ->color(fn(bool $state) => $state ? 'success' : 'warning'),
                        TextEntry::make('catatan_admin')
                            ->label('Catatan / Keputusan')
                            ->placeholder('Belum ada catatan')
                            ->columnSpanFull(),
                    ]),
                ]),
            Section::make('Mata Kuliah')
                ->schema([
                    RepeatableEntry::make('details')
                        ->label('')
                        ->schema([
                            TextEntry::make('kode_mk_snapshot')->label('Kode'),
                            TextEntry::make('nama_mk_snapshot')->label('Mata Kuliah'),
                            TextEntry::make('sks_snapshot')->label('SKS')->badge(),
                            TextEntry::make('jadwalKuliah.hari')->label('Hari')->placeholder('-'),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
