<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LpmKuisionerKelompokForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kelompok')
                    ->label('Nama Kelompok Kuisioner')
                    ->required()
                    ->maxLength(255),
                Select::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'EDOM' => 'Evaluasi Dosen oleh Mahasiswa',
                        'MONEV' => 'Monitoring & Evaluasi',
                        'KEPUASAN_MAHASISWA' => 'Kepuasan Mahasiswa',
                        'KEPUASAN_DOSEN' => 'Kepuasan Dosen',
                        'KEPUASAN_TENDIK' => 'Kepuasan Tenaga Kependidikan',
                        'KEPUASAN_ALUMNI' => 'Kepuasan Alumni',
                        'KEPUASAN_PENGGUNA_LULUSAN' => 'Kepuasan Pengguna Lulusan',
                    ])
                    ->required()
                    ->helperText('EDOM memakai jawaban lewat KRS mahasiswa (lpm_edom_jawaban). KEPUASAN_MAHASISWA memakai lpm_survey_jawaban. Kategori lain (Dosen/Tendik/Alumni/Pengguna Lulusan) memakai lpm_survey_jawaban_pihak.'),
                Select::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->searchable()
                    ->preload(),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
