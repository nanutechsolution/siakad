<?php

namespace App\Filament\Resources\LpmDokumens\Schemas;

use App\Models\LpmStandar;
use App\Models\RefProdi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LpmDokumenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dokumen')
                    ->columns(2)
                    ->schema([
                        TextInput::make('kode_dokumen')
                            ->label('Kode Dokumen')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nama_dokumen')
                            ->label('Nama Dokumen')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('jenis')
                            ->label('Jenis Dokumen')
                            ->options([
                                'KEBIJAKAN' => 'Kebijakan Mutu',
                                'MANUAL' => 'Manual Mutu',
                                'STANDAR' => 'Standar Mutu',
                                'FORMULIR' => 'Formulir',
                                'SOP' => 'SOP',
                                'DOKUMEN_PENDUKUNG' => 'Dokumen Pendukung',
                            ])
                            ->required(),
                        TextInput::make('versi')
                            ->label('Versi')
                            ->default('1.0')
                            ->required(),
                        Select::make('unit_kerja_id')
                            ->label('Unit Pemilik')
                            ->relationship('unitKerja', 'nama_unit')
                            ->searchable()
                            ->preload(),
                        Select::make('prodi_id')
                            ->label('Program Studi (opsional)')
                            ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                            ->searchable(),
                        Select::make('standar_id')
                            ->label('Standar Terkait (opsional)')
                            ->options(fn() => LpmStandar::query()->pluck('nama_standar', 'id'))
                            ->searchable(),
                        DatePicker::make('tgl_berlaku')
                            ->label('Tanggal Berlaku')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'DRAFT' => 'Draft',
                                'REVIEW' => 'Review',
                                'PUBLISHED' => 'Disahkan',
                                'ARCHIVED' => 'Kadaluarsa',
                            ])
                            ->default('DRAFT')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Berkas & Deskripsi')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('File Dokumen')
                            ->directory('lpm/dokumen')
                            ->required(),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
