<?php

namespace App\Filament\Resources\PaymentPolicies\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentPolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make('Informasi Utama')->schema([
                        TextInput::make('nama')
                            ->label('Nama Kebijakan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('tahun_akademik_id')
                            ->label('Tahun Akademik')
                            ->relationship('tahunAkademik', 'nama_tahun')
                            ->required(),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                    Section::make('Target / Ruang Lingkup (Opsional)')
                        ->description('Kosongkan jika berlaku untuk semua.')
                        ->schema([
                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->relationship('prodi', 'nama_prodi')
                                ->searchable()
                                ->preload(),

                            Select::make('program_kelas_id')
                                ->label('Program Kelas')
                                ->relationship('programKelas', 'nama_program')
                                ->searchable()
                                ->preload(),

                            TextInput::make('angkatan')
                                ->label('Angkatan (Tahun)')
                                ->numeric()
                                ->maxLength(4),
                        ])->columns(3),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('Rincian Komponen Pembayaran')
                        ->description('Atur persentase atau nominal minimal untuk syarat KRS.')
                        ->schema([
                            Repeater::make('details')
                                ->relationship('details')
                                ->label('')
                                ->schema([
                                    Select::make('komponen_biaya_id')
                                        ->label('Komponen Biaya')
                                        ->relationship('komponenBiaya', 'nama_komponen')
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->required(),

                                    Toggle::make('wajib')
                                        ->label('Wajib Lunas/DP untuk KRS?')
                                        ->default(true),

                                    TextInput::make('minimal_persen')
                                        ->label('Minimal Persen (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->step(0.01)
                                        ->default(100.00)
                                        ->required(),

                                    TextInput::make('minimal_nominal')
                                        ->label('Atau Minimal Nominal (Rp)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->helperText('Abaikan jika menggunakan persentase.')
                                        ->nullable(),
                                ])
                                ->columns(1)
                                ->itemLabel(fn(array $state): ?string => 'Komponen Setup')
                                ->addActionLabel('Tambah Komponen Biaya')
                                ->collapsible()
                                ->defaultItems(1),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
