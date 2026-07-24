<?php

namespace App\Filament\Resources\LpmSurveyJawabanPihaks\Schemas;

use App\Models\LpmKuisionerPertanyaan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LpmSurveyJawabanPihakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jenis_responden')
                    ->label('Jenis Responden')
                    ->options([
                        'DOSEN' => 'Dosen',
                        'TENDIK' => 'Tenaga Kependidikan',
                        'ALUMNI' => 'Alumni',
                        'PENGGUNA_LULUSAN' => 'Pengguna Lulusan',
                    ])
                    ->required()
                    ->live(),
                Select::make('person_id')
                    ->label('Nama (Internal)')
                    ->relationship('person', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->visible(fn(Get $get) => in_array($get('jenis_responden'), ['DOSEN', 'TENDIK'], true))
                    ->required(fn(Get $get) => in_array($get('jenis_responden'), ['DOSEN', 'TENDIK'], true)),
                TextInput::make('nama_eksternal')
                    ->label('Nama')
                    ->maxLength(255)
                    ->visible(fn(Get $get) => in_array($get('jenis_responden'), ['ALUMNI', 'PENGGUNA_LULUSAN'], true))
                    ->required(fn(Get $get) => in_array($get('jenis_responden'), ['ALUMNI', 'PENGGUNA_LULUSAN'], true)),
                TextInput::make('instansi_eksternal')
                    ->label('Instansi')
                    ->maxLength(255)
                    ->visible(fn(Get $get) => in_array($get('jenis_responden'), ['ALUMNI', 'PENGGUNA_LULUSAN'], true)),
                Select::make('pertanyaan_id')
                    ->label('Pertanyaan')
                    ->options(function (Get $get) {
                        $jenis = $get('jenis_responden');

                        if (! $jenis) {
                            return [];
                        }

                        return LpmKuisionerPertanyaan::query()
                            ->whereHas('kelompok', fn($query) => $query->where('kategori', 'KEPUASAN_' . $jenis))
                            ->orderBy('urutan')
                            ->pluck('bunyi_pertanyaan', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Daftar pertanyaan mengikuti kelompok kuisioner berkategori KEPUASAN_<jenis responden>.'),
                Select::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('jawaban_nilai')
                    ->label('Jawaban')
                    ->rows(2)
                    ->required()
                    ->helperText('Isi angka untuk pertanyaan rating (mis. 1-5), atau teks untuk pertanyaan esai.'),
            ]);
    }
}
