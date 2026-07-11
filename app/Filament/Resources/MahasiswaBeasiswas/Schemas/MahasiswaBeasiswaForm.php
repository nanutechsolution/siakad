<?php

namespace App\Filament\Resources\MahasiswaBeasiswas\Schemas;

use App\Models\Mahasiswa;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBeasiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penerima & Program Beasiswa')
                    ->schema([
                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa Penerima')
                            ->relationship(name: 'mahasiswa', titleAttribute: 'nim')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nim} - " . ($record->person->nama_lengkap ?? 'Unknown'))
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return Mahasiswa::with('person')
                                    ->where('nim', 'like', "%{$search}%")
                                    ->orWhereHas('person', function ($query) use ($search) {
                                        $query->where('nama_lengkap', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($m) => [$m->id => "{$m->nim} - " . ($m->person->nama_lengkap ?? '')]);
                            })
                            ->preload()
                            ->required()
                            ->live(),

                        Select::make('beasiswa_id')
                            ->label('Program Beasiswa')
                            ->relationship('beasiswa', 'nama_beasiswa')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->rules([
                                fn(Get $get, ?Model $record) => new \App\Rules\Keuangan\CekOverlapBeasiswaKomponen(
                                    mahasiswaId: (string) $get('mahasiswa_id'),
                                    beasiswaIdDiusulkan: (int) $get('beasiswa_id'),
                                    isOverlapAllowed: (bool) $get('is_overlap_allowed'),
                                    ignoreRecordId: $record?->id
                                )
                            ]),

                        Toggle::make('is_overlap_allowed')
                            ->label('Izinkan Overlap Diskon')
                            ->helperText('Centang jika Anda secara eksplisit mengizinkan mahasiswa ini menerima lebih dari satu beasiswa pada komponen biaya yang sama (Double Discount).')
                            ->dehydrated(false) // Field virtual, tidak disimpan ke DB
                            ->onColor('danger')
                            ->live(),
                    ]),

                Section::make('Periode & Legalitas')
                    ->schema([
                        Select::make('tahun_akademik_mulai_id')
                            ->label('Tahun Akademik Mulai')
                            ->relationship('tahunAkademikMulai', 'nama_tahun')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('tahun_akademik_akhir_id')
                            ->label('Tahun Akademik Selesai')
                            ->relationship('tahunAkademikAkhir', 'nama_tahun')
                            ->searchable()
                            ->preload()
                            ->placeholder('Kosongkan jika beasiswa tidak terbatas waktu')
                            ->helperText('Beasiswa akan otomatis berhenti setelah tahun akademik ini terlampaui.'),
                        TextInput::make('nomor_sk')
                            ->label('Nomor SK / Referensi Legal')
                            ->maxLength(100)
                            ->placeholder('Misal: SK-B/100/2026'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
