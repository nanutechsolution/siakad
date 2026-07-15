<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs\Schemas;

use App\Enums\KrsStatusEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RiwayatKrsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('tahunAkademik.nama_tahun')->label('Semester'),
                        TextEntry::make('status_krs')
                            ->label('Status Terakhir')
                            ->badge()
                            ->color(fn(KrsStatusEnum $state): string => $state->getColor())
                            ->formatStateUsing(fn(KrsStatusEnum $state): string => $state->getLabel()),
                        TextEntry::make('total_sks_diambil')->label('Total SKS Diambil'),

                        // Menampilkan catatan dari dosen wali/admin jika ada
                        TextEntry::make('catatan_admin')
                            ->label('Catatan Dosen Wali')
                            ->visible(fn($record) => filled($record->catatan_admin))
                            ->columnSpanFull()
                            ->color(fn($record) => $record->status_krs === KrsStatusEnum::DITOLAK ? 'danger' : 'gray'),
                    ])->columns(3),

                Section::make('Daftar Mata Kuliah Diambil')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                TextEntry::make('kode_mk_snapshot')
                                    ->label('Kode MK')
                                    ->state(fn($record) => $record->kode_mk_snapshot ?? $record->mataKuliah?->kode_mk),

                                TextEntry::make('nama_mk_snapshot')
                                    ->label('Mata Kuliah')
                                    ->weight('bold')
                                    ->state(fn($record) => $record->nama_mk_snapshot ?? $record->mataKuliah?->nama_mk),

                                TextEntry::make('sks_snapshot')
                                    ->label('SKS')
                                    ->badge()
                                    ->state(fn($record) => $record->sks_snapshot ?? $record->mataKuliah?->sks_default),

                                TextEntry::make('jadwal')
                                    ->label('Jadwal & Ruang')
                                    ->state(function ($record) {
                                        if (!$record->jadwalKuliah) return 'Belum ada jadwal';

                                        $hari = $record->jadwalKuliah->hari;
                                        $jam = substr($record->jadwalKuliah->jam_mulai, 0, 5) . ' - ' . substr($record->jadwalKuliah->jam_selesai, 0, 5);
                                        $ruang = $record->jadwalKuliah->ruang?->nama_ruang ?? '-';

                                        return "{$hari}, {$jam} | Rgn: {$ruang}";
                                    }),
                            ])
                            ->columns(4)
                    ])
            ]);
    }
}
