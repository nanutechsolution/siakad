<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Kelas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        // Buat closure untuk mengecek apakah kelas sudah punya mahasiswa
        $hasStudents = function (?Kelas $record) {
            if (!$record) return false;
            
            return DB::table('mahasiswa_kelas')
                ->where('kelas_id', $record->id)
                ->whereNull('tanggal_keluar') // Pastikan cek yang aktif saja
                ->exists();
        };

        return $schema
            ->components([
                Section::make('Informasi Master Kelas')
                    ->description('Pastikan kombinasi nama, prodi, program, dan angkatan belum pernah terdaftar. Beberapa data akan dikunci jika kelas sudah terisi mahasiswa.')
                    ->schema([
                        TextInput::make('nama_kelas')
                            ->label('Nama Kelas')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kelas A, Reguler Sore A')
                            ->unique(
                                table: 'kelas',
                                column: 'nama_kelas',
                                modifyRuleUsing: function (Unique $rule, Get $get, ?Kelas $record) {
                                    return $rule
                                        ->where('prodi_id', $get('prodi_id'))
                                        ->where('program_id', $get('program_id'))
                                        ->where('angkatan_id', $get('angkatan_id'))
                                        ->ignore($record?->id);
                                }
                            ),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->required()
                            ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                            ->searchable()
                            ->preload()
                            ->disabled($hasStudents)
                            ->dehydrated(), // Penting: agar nilai tetap dikirim saat form disimpan meski di-disable

                        Select::make('program_id')
                            ->label('Program Kelas')
                            ->required()
                            ->options(DB::table('ref_program')->where('is_active', 1)->pluck('nama_program', 'id'))
                            ->searchable()
                            ->preload()
                            ->disabled($hasStudents)
                            ->dehydrated(),

                        Select::make('angkatan_id')
                            ->label('Angkatan')
                            ->required()
                            ->options(DB::table('ref_angkatan')->orderBy('id_tahun', 'desc')->pluck('id_tahun', 'id_tahun'))
                            ->searchable()
                            ->preload()
                            ->disabled($hasStudents)
                            ->dehydrated(),

                        TextInput::make('kapasitas')
                            ->label('Kapasitas Kelas (Mahasiswa)')
                            ->numeric()
                            ->default(40)
                            ->minValue(1)
                            ->required(),
                    ])->columns(2),
            ])->columns(1);
    }
}