<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas;

use App\Enums\KrsStatusEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBimbinganInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Mahasiswa')
                    ->description('Informasi dasar mahasiswa bimbingan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nim')
                                    ->label('NIM')
                                    ->weight('bold'),

                                TextEntry::make('person.nama_lengkap')
                                    ->label('Nama Lengkap'),

                                TextEntry::make('prodi.nama_prodi')
                                    ->label('Program Studi'),

                                TextEntry::make('angkatan.id_tahun')
                                    ->label('Tahun Angkatan'),
                            ]),
                    ])->collapsible(),

                Section::make('Status KRS Aktif')
                    ->description('Informasi status Kartu Rencana Studi pada semester berjalan')
                    ->schema([
                        TextEntry::make('krs_status')
                            ->label('Status Pengajuan KRS')
                            ->badge()
                            // Gunakan relasi krs yang sudah di eager-load dari Resource getEloquentQuery()
                            ->state(fn(Model $record) => $record->krs->first()?->status_krs?->value ?? 'BELUM AJUAN')
                            ->color(fn(string $state) => match ($state) {
                                KrsStatusEnum::DISETUJUI->value => KrsStatusEnum::DISETUJUI->getColor(),
                                KrsStatusEnum::DIAJUKAN->value => KrsStatusEnum::DIAJUKAN->getColor(),
                                KrsStatusEnum::DITOLAK->value => KrsStatusEnum::DITOLAK->getColor(),
                                default => 'gray',
                            }),
                    ]),
            ]);
    }
}
