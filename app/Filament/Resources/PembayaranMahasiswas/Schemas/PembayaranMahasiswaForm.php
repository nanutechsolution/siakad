<?php

namespace App\Filament\Resources\PembayaranMahasiswas\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PembayaranMahasiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pembayaran')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('tagihan.kode_transaksi')
                                ->label('Kode Transaksi Tagihan')
                                ->disabled(),
                            TextInput::make('nominal_bayar')
                                ->label('Nominal Bayar')
                                ->prefix('Rp')
                                ->disabled(),
                            TextInput::make('metode_pembayaran')
                                ->label('Metode')
                                ->disabled(),
                            TextInput::make('tanggal_bayar')
                                ->label('Tgl Bayar')
                                ->disabled(),
                        ]),
                        FileUpload::make('bukti_bayar_path')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->downloadable()
                            ->disabled(),
                    ]),
            ]);
    }
}
