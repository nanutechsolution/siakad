<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Enums\HR\JenisPegawai;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kepegawaian')
                    ->schema([
                        Select::make('person_id')
                            ->label('Identitas Person (SSOT)')
                            ->relationship('person', 'nama_lengkap')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Grid::make(2)->schema([
                                    TextInput::make('nama_lengkap')
                                        ->label('Nama Lengkap (Sesuai KTP)')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                    TextInput::make('nik')
                                        ->label('NIK / No. KTP')
                                        ->unique('ref_person', 'nik', ignoreRecord: true)
                                        ->maxLength(255),
                                    Select::make('jenis_kelamin')
                                        ->label('Jenis Kelamin')
                                        ->options([
                                            'L' => 'Laki-laki',
                                            'P' => 'Perempuan',
                                        ]),
                                    TextInput::make('email')
                                        ->label('Email Pribadi')
                                        ->email()
                                        ->maxLength(255),
                                    TextInput::make('no_hp')
                                        ->label('No. Handphone')
                                        ->maxLength(20),
                                    TextInput::make('tempat_lahir')
                                        ->label('Tempat Lahir')
                                        ->maxLength(255),
                                    DatePicker::make('tanggal_lahir')
                                        ->label('Tanggal Lahir')
                                        ->native(false)
                                        ->displayFormat('d/m/Y'),
                                ]),
                            ])
                            ->columnSpanFull(),

                        TextInput::make('nip')
                            ->label('Nomor Induk Pegawai (NIP)')
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->placeholder('Kosongkan jika belum memiliki NIP'),

                        Select::make('jenis_pegawai')
                            ->label('Status Kepegawaian')
                            ->options(JenisPegawai::class)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Status Aktif Pegawai')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
