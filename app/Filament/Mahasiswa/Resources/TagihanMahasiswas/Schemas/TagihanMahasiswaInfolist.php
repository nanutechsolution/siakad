<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TagihanMahasiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Invoice')
                    ->schema([
                        TextEntry::make('kode_transaksi')->label('Nomor Invoice'),
                        TextEntry::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                        TextEntry::make('status_bayar')
                            ->label('Status Pembayaran')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'LUNAS' => 'success',
                                'CICIL' => 'warning',
                                'BELUM' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3),

                Section::make('Rincian Komponen Biaya')
                    ->description('Daftar kewajiban item pembiayaan Anda di semester ini.')
                    ->schema([
                        ViewEntry::make('details')
                            ->label('')
                            ->view('filament.mahasiswa.components.tagihan-details-table'),
                    ]),
            ]);
    }
}
