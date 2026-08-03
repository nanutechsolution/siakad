<?php

namespace App\Filament\Resources\KonfigurasiPembimbingAkademiks\Schemas;

use App\Enums\PembimbingAkademikMode;
use App\Models\RefAngkatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KonfigurasiPembimbingAkademikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konfigurasi Mode Pembimbing')
                ->description('Menentukan bagaimana pembimbing akademik ditetapkan untuk satu angkatan pada satu program studi — per kelas atau per mahasiswa.')
                ->columnSpanFull()
                ->components([
                    Select::make('prodi_id')
                        ->label('Program Studi')
                        ->relationship('prodi', 'nama_prodi')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn($rule, $get) => $rule->where('angkatan_id', $get('angkatan_id')),
                        )
                        ->validationMessages([
                            'unique' => 'Kombinasi Program Studi dan Angkatan ini sudah memiliki konfigurasi.',
                        ]),
                    Select::make('angkatan_id')
                        ->label('Angkatan')
                        ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                        ->searchable()
                        ->required()
                        ->live(),
                    Select::make('mode')
                        ->label('Mode Penetapan')
                        ->options(PembimbingAkademikMode::options())
                        ->default(PembimbingAkademikMode::PER_KELAS)
                        ->required()
                        ->native(false)
                        ->helperText('Per Kelas: satu dosen wali berlaku untuk satu kelas. Per Mahasiswa: dosen wali ditetapkan satu per satu ke tiap mahasiswa.'),
                    Toggle::make('aktif')
                        ->label('Aktif')
                        ->default(true)
                        ->helperText('Nonaktifkan bila konfigurasi ini belum/tidak digunakan.'),
                    Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->columnSpanFull()
                        ->rows(3),
                ]),
        ]);
    }
}
