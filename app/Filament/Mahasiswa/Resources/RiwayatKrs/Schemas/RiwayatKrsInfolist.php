<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Schemas;

use App\Enums\KrsStatusEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class RiwayatKrsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Bagian Informasi Utama dengan gaya Header Card Minimalis
                Section::make()
                    ->schema([
                        TextEntry::make('tahunAkademik.nama_tahun')
                            ->label('Tahun Akademik / Semester')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->color('primary'),

                        TextEntry::make('total_sks_diambil')
                            ->label('Beban Studi')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state) => "{$state} SKS"),

                        TextEntry::make('status_krs')
                            ->label('Status Pengajuan')
                            ->badge()
                            ->color(fn (KrsStatusEnum $state): string => $state->getColor())
                            ->formatStateUsing(fn (KrsStatusEnum $state): string => $state->getLabel()),

                        // Catatan Dosen Wali dengan desain alert modern (soft background)
                        TextEntry::make('catatan_admin')
                            ->label('Catatan & Evaluasi Dosen Wali')
                            ->visible(fn ($record) => filled($record->catatan_admin))
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => "“ {$state} ”")
                            ->color(fn ($record) => $record->status_krs === KrsStatusEnum::DITOLAK ? 'danger' : 'warning')
                    ])
                    ->columns(3)
                    ->extraAttributes([
                        'class' => 'rounded-2xl border border-gray-100 bg-white/80 p-2 shadow-sm backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/50',
                    ]),

                // Bagian Daftar Mata Kuliah dengan gaya list ber-card interaktif
                Section::make('Mata Kuliah yang Ditempuh')
                    ->description('Daftar mata kuliah yang diambil beserta jadwal perkuliahan aktif.')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                TextEntry::make('kode_mk_snapshot')
                                    ->label('Kode')
                                    ->badge()
                                    ->color('gray')
                                    ->state(fn ($record) => $record->kode_mk_snapshot ?? $record->mataKuliah?->kode_mk),

                                TextEntry::make('nama_mk_snapshot')
                                    ->label('Mata Kuliah')
                                    ->weight('bold')
                                    ->size(TextSize::Medium)
                                    ->state(fn ($record) => $record->nama_mk_snapshot ?? $record->mataKuliah?->nama_mk),

                                TextEntry::make('sks_snapshot')
                                    ->label('Bobot')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn ($state) => "{$state} SKS")
                                    ->state(fn ($record) => $record->sks_snapshot ?? $record->mataKuliah?->sks_default),

                                TextEntry::make('jadwal')
                                    ->label('Jadwal & Ruangan')
                                    ->icon('heroicon-m-calendar-days')
                                    ->iconColor('primary')
                                    ->state(function ($record) {
                                        if (!$record->jadwalKuliah) return 'Belum ada jadwal terplot';

                                        $hari = $record->jadwalKuliah->hari;
                                        $jam = substr($record->jadwalKuliah->jam_mulai, 0, 5) . ' - ' . substr($record->jadwalKuliah->jam_selesai, 0, 5);
                                        $ruang = $record->jadwalKuliah->ruang?->nama_ruang ?? 'Ruangan belum ditentukan';

                                        return "{$hari}, {$jam} • {$ruang}";
                                    }),
                            ])
                            // Mengatur grid per item repeatable agar responsif dan rapi ala card 2026
                            ->columns([
                                'default' => 1,
                                'md' => 4,
                            ])
                    ])
                    ->extraAttributes([
                        'class' => 'rounded-2xl border border-gray-100 bg-white/80 p-2 shadow-sm backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/50 mt-4',
                    ]),
            ]);
    }
}