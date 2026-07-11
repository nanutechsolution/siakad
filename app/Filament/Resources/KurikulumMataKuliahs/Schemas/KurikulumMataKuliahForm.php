<?php

namespace App\Filament\Resources\KurikulumMataKuliahs\Schemas;

use App\Models\KurikulumMataKuliah;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class KurikulumMataKuliahForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make('Data Pemetaan')
                        ->schema([
                            Select::make('kurikulum_id')
                                ->label('Kurikulum')
                                ->relationship('kurikulum', 'nama_kurikulum')
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('mata_kuliah_id')
                                ->label('Mata Kuliah')
                                ->relationship('mataKuliah', 'nama_mk')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->unique(
                                    modifyRuleUsing: function (Get $get, $rule) {
                                        return $rule->where('kurikulum_id', $get('kurikulum_id'));
                                    },
                                )
                                ->helperText('Satu mata kuliah hanya bisa dipetakan sekali dalam satu kurikulum yang sama.'),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('semester_paket')
                                        ->label('Semester Paket')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(8)
                                        ->placeholder('Cth: 1'),

                                    Select::make('sifat_mk')
                                        ->label('Sifat Mata Kuliah')
                                        ->required()
                                        ->options([
                                            'W' => 'Wajib',
                                            'P' => 'Pilihan',
                                        ])
                                        ->default('W'),
                                ]),
                        ]),

                    // Penggunaan Repeater untuk Prasyarat
                    Section::make('Mata Kuliah Prasyarat')
                        ->description('Tambahkan syarat mata kuliah yang harus diambil sebelum mengambil MK ini.')
                        ->schema([
                            Repeater::make('syaratPrasyarat')
                                ->relationship('syaratPrasyarat')
                                ->label('')
                                ->schema([
                                    Select::make('prasyarat_kurikulum_mk_id')
                                        ->label('Pilih MK Prasyarat')
                                        ->options(function (Get $get, ?KurikulumMataKuliah $record) {
                                            // Hanya tampilkan MK di kurikulum yang sama, hindari memilih diri sendiri
                                            $kurikulumId = $get('../../kurikulum_id') ?? $record?->kurikulum_id;

                                            if (!$kurikulumId) {
                                                return [];
                                            }

                                            $query = KurikulumMataKuliah::query()
                                                ->where('kurikulum_id', $kurikulumId)
                                                ->with('mataKuliah');

                                            if ($record) {
                                                $query->where('id', '!=', $record->id);
                                            }

                                            return $query->get()->pluck('mataKuliah.nama_mk', 'id');
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload(),

                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('min_nilai_huruf')
                                                ->label('Min. Huruf')
                                                ->required()
                                                ->maxLength(2)
                                                ->default('D'),

                                            TextInput::make('min_nilai')
                                                ->label('Min. Angka (IP)')
                                                ->required()
                                                ->numeric()
                                                ->default(2.00)
                                                ->step(0.01),

                                            Select::make('logic_type')
                                                ->label('Logika Syarat')
                                                ->required()
                                                ->options([
                                                    'AND' => 'AND (Wajib)',
                                                    'OR' => 'OR (Opsional)',
                                                ])
                                                ->default('AND'),
                                        ]),
                                ])
                                ->itemLabel(fn(array $state): ?string => 'Prasyarat Baru')
                                ->addActionLabel('Tambah Prasyarat')
                                ->collapsible()
                                ->defaultItems(0)
                                // Disable repeater jika kurikulum belum dipilih
                                ->disabled(fn(Get $get): bool => blank($get('kurikulum_id'))),
                        ]),
                ]),

                Group::make([
                    Section::make('Distribusi SKS Aktual')
                        ->description('Override SKS jika berbeda dengan SKS Default Master.')
                        ->schema([
                            TextInput::make('sks_tatap_muka')
                                ->label('SKS Tatap Muka')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            TextInput::make('sks_praktek')
                                ->label('SKS Praktek')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            TextInput::make('sks_lapangan')
                                ->label('SKS Lapangan')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ])
            ]);
    }
}
