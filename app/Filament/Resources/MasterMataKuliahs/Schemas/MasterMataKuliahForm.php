<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterMataKuliahs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MasterMataKuliahForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make('Identitas Mata Kuliah')
                        ->schema([
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->relationship('prodi', 'nama_prodi')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->validationMessages([
                                    'required' => 'Program Studi wajib dipilih.',
                                ]),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('kode_mk')
                                        ->label('Kode Mata Kuliah')
                                        ->required()
                                        ->minLength(2)
                                        ->maxLength(20)
                                        ->regex('/^[A-Z0-9]+$/')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(
                                            fn (Set $set, ?string $state) => $set('kode_mk', strtoupper(trim((string) $state)))
                                        )
                                        ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                                        // Unique kombinasi prodi_id + kode_mk, aman untuk create maupun edit
                                        ->unique(
                                            table: 'master_mata_kuliahs',
                                            column: 'kode_mk',
                                            ignoreRecord: true,
                                            modifyRuleUsing: function (Get $get, $rule) {
                                                return $rule->where('prodi_id', $get('prodi_id'));
                                            },
                                        )
                                        ->validationMessages([
                                            'required' => 'Kode Mata Kuliah wajib diisi.',
                                            'regex' => 'Kode Mata Kuliah hanya boleh berisi huruf kapital dan angka tanpa spasi (cth: MKU101).',
                                            'unique' => 'Kode Mata Kuliah ini sudah dipakai pada Program Studi yang sama.',
                                        ])
                                        ->placeholder('Cth: MKU101'),

                                    TextInput::make('nama_mk')
                                        ->label('Nama Mata Kuliah')
                                        ->required()
                                        ->minLength(5)
                                        ->maxLength(200)
                                        ->regex('/^[\p{L}\p{N}\s\.\,\-\/\(\)]+$/u')
                                        ->validationMessages([
                                            'required' => 'Nama Mata Kuliah wajib diisi.',
                                            'min' => 'Nama Mata Kuliah minimal 5 karakter.',
                                            'regex' => 'Nama Mata Kuliah mengandung karakter yang tidak diizinkan.',
                                        ])
                                        ->placeholder('Cth: Pendidikan Agama'),
                                ]),
                        ]),

                    Section::make('Distribusi SKS')
                        ->description('Total SKS harus sama dengan penjumlahan SKS Tatap Muka, Praktek, dan Lapangan.')
                        ->schema([
                            TextInput::make('sks_default')
                                ->label('Total SKS')
                                ->required()
                                ->numeric()
                                ->integer()
                                ->default(3)
                                ->minValue(1)
                                ->maxValue(6)
                                ->live(onBlur: true)
                                ->rules([
                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $sum = (int) $get('sks_tatap_muka')
                                            + (int) $get('sks_praktek')
                                            + (int) $get('sks_lapangan');

                                        if ($sum !== (int) $value) {
                                            $fail("Total SKS ({$value}) tidak sama dengan penjumlahan rincian SKS ({$sum}). Periksa kembali SKS Tatap Muka, Praktek, dan Lapangan.");
                                        }
                                    },
                                ])
                                ->validationMessages([
                                    'required' => 'Total SKS wajib diisi.',
                                    'min' => 'Total SKS minimal 1.',
                                    'max' => 'Total SKS maksimal 6.',
                                ]),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sks_tatap_muka')
                                        ->label('SKS Tatap Muka')
                                        ->required()
                                        ->numeric()
                                        ->integer()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(6)
                                        ->live(onBlur: true),

                                    TextInput::make('sks_praktek')
                                        ->label('SKS Praktek')
                                        ->required()
                                        ->numeric()
                                        ->integer()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(6)
                                        ->live(onBlur: true),

                                    TextInput::make('sks_lapangan')
                                        ->label('SKS Lapangan')
                                        ->required()
                                        ->numeric()
                                        ->integer()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(6)
                                        ->live(onBlur: true),
                                ]),
                        ]),
                ]),
                Group::make([
                    Section::make('Kategorisasi')
                        ->schema([
                            Select::make('jenis_mk')
                                ->label('Jenis MK')
                                ->required()
                                ->options([
                                    'A' => 'Wajib',
                                    'B' => 'Pilihan',
                                    'C' => 'Wajib Peminatan',
                                    'D' => 'Pilihan Peminatan',
                                    'S' => 'Tugas Akhir/Skripsi',
                                ])
                                ->default('A')
                                // Guard tambahan di server-side, bukan hanya batasan UI Select
                                ->rules(['in:A,B,C,D,S'])
                                ->validationMessages([
                                    'required' => 'Jenis Mata Kuliah wajib dipilih.',
                                    'in' => 'Jenis Mata Kuliah tidak valid.',
                                ]),

                            Select::make('activity_type')
                                ->label('Tipe Aktivitas')
                                ->required()
                                ->options([
                                    'REGULAR' => 'REGULAR',
                                    'MBKM' => 'MBKM',
                                    'PRAKTIK KERJA' => 'PRAKTIK KERJA',
                                ])
                                ->default('REGULAR')
                                ->rules(['in:REGULAR,MBKM,PRAKTIK KERJA'])
                                ->validationMessages([
                                    'required' => 'Tipe Aktivitas wajib dipilih.',
                                    'in' => 'Tipe Aktivitas tidak valid.',
                                ]),
                        ]),
                ]),
            ]);
    }
}