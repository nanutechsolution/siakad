<?php

namespace App\Filament\Resources\RefAturanSks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefAturanSksForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konfigurasi Batas SKS')
                    ->description('Sistem akan menolak penginputan jika rentang IPS tumpang tindih.')
                    ->schema([
                        TextInput::make('min_ips')
                            ->required()
                            ->numeric()
                            ->maxValue(4)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if ($get('max_ips') !== null && $state >= $get('max_ips')) {
                                    $set('max_ips', $state + 0.1); // Auto-correct atau biarkan validasi menangkapnya
                                }
                            }),

                        TextInput::make('max_ips')
                            ->required()
                            ->numeric()
                            ->maxValue(4)
                            ->rules([
                                fn($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if ($value <= $get('min_ips')) {
                                        $fail('Max IPS harus lebih besar dari Min IPS.');
                                    }
                                },
                            ]),

                        TextInput::make('max_sks')
                            ->label('Beban Maksimal SKS')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->helperText('SKS maksimal yang boleh diambil.'),
                    ])->columnSpanFull()
            ]);
    }
}
