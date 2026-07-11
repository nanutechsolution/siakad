<?php

namespace App\Filament\Resources\RefPeople\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefPersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Identitas')
                            ->description('Identitas utama berdasarkan dokumen resmi.')
                            ->schema([
                                TextInput::make('nik')
                                    ->label('NIK (Nomor Induk Kependudukan)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->numeric()
                                    ->length(16)
                                    ->columnSpanFull(),

                                TextInput::make('nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->maxLength(255),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->displayFormat('d F Y')
                                            ->native(false),
                                    ]),

                                Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan',
                                    ])
                                    ->native(false)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Foto Profil')
                            ->schema([
                                FileUpload::make('photo_path')
                                    ->label('Unggah Foto')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('person-photos')
                                    ->maxSize(2048)
                                    ->avatar()
                                    ->alignCenter(),
                            ]),

                        Section::make('Kontak')
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email Pribadi')
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('no_hp')
                                    ->label('Nomor HP/WhatsApp')
                                    ->tel()
                                    ->maxLength(20),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
