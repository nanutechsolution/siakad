<?php

namespace App\Filament\Resources\RefSkalaNilais\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefSkalaNilaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Skala Nilai')
                    ->schema([
                        TextInput::make('huruf')
                            ->label('Nilai Huruf')
                            ->required()
                            ->maxLength(2)
                            ->placeholder('A'),

                        TextInput::make('bobot_indeks')
                            ->label('Bobot Indeks')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(4)
                            ->step(0.01),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('nilai_min')
                                    ->label('Nilai Minimum')
                                    ->required()
                                    ->numeric()
                                    ->maxValue(999.99) // Harus di bawah kapasitas DB
                                    ->rules([
                                        fn($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $max = $get('nilai_max');
                                            if ($max !== null && $value >= $max) {
                                                $fail('Nilai Minimum harus lebih kecil dari Nilai Maksimum.');
                                            }
                                        },
                                    ]),

                                TextInput::make('nilai_max')
                                    ->label('Nilai Maksimum')
                                    ->required()
                                    ->numeric()
                                    ->maxValue(999.99)
                                    ->rules([
                                        fn($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $min = $get('nilai_min');
                                            if ($min !== null && $value <= $min) {
                                                $fail('Nilai Maksimum harus lebih besar dari Nilai Minimum.');
                                            }
                                        },
                                    ]),
                            ]),

                        Toggle::make('is_lulus')
                            ->label('Status Kelulusan')
                            ->helperText('Apakah nilai ini dianggap lulus?')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
            ]);
    }
}
