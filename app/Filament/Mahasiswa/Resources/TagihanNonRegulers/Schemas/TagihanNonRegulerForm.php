<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagihanNonRegulerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mahasiswa_id')
                    ->required(),
                TextInput::make('kode_transaksi')
                    ->required(),
                TextInput::make('deskripsi')
                    ->required(),
                TextInput::make('total_tagihan')
                    ->required()
                    ->numeric(),
                TextInput::make('total_bayar')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status_bayar')
                    ->options(['BELUM' => 'B e l u m', 'CICIL' => 'C i c i l', 'LUNAS' => 'L u n a s'])
                    ->default('BELUM')
                    ->required(),
                TextInput::make('referensi_type'),
                TextInput::make('referensi_id'),
                DatePicker::make('tenggat_waktu'),
                TextInput::make('created_by'),
            ]);
    }
}
