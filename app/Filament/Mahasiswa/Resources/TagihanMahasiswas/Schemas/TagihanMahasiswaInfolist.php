<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TagihanMahasiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Ringkasan Invoice
                |--------------------------------------------------------------------------
                */

                Section::make()
                    ->schema([
                        ViewEntry::make('summary')
                            ->hiddenLabel()
                            ->view('filament.mahasiswa.tagihan.invoice-summary'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Komponen Tagihan
                |--------------------------------------------------------------------------
                */

                Section::make('Rincian Komponen Biaya')
                    ->description('Daftar seluruh komponen tagihan semester ini.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        ViewEntry::make('details')
                            ->hiddenLabel()
                            ->view('filament.mahasiswa.tagihan.invoice-components'),
                    ])
                    ->collapsible(),

                /*
                |--------------------------------------------------------------------------
                | Riwayat Pembayaran
                |--------------------------------------------------------------------------
                */

                Section::make('Riwayat Pembayaran')
                    ->description('Seluruh pembayaran yang pernah Anda lakukan.')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        ViewEntry::make('payments')
                            ->hiddenLabel()
                            ->view('filament.mahasiswa.tagihan.invoice-payments'),
                    ])
                    ->collapsible(),

                /*
                |--------------------------------------------------------------------------
                | Konfirmasi Pembayaran
                |--------------------------------------------------------------------------
                */

                Section::make('Konfirmasi Pembayaran')
                    ->description('Upload bukti transfer apabila pembayaran dilakukan secara manual.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        ViewEntry::make('upload')
                            ->hiddenLabel()
                            ->view('filament.mahasiswa.tagihan.invoice-upload'),
                    ])
                    ->visible(fn($record) => $record->status_bayar !== 'LUNAS'),

            ]);
    }
}
