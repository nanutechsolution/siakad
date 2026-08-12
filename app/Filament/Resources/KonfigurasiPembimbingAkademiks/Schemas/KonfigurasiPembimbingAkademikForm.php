<?php

namespace App\Filament\Resources\KonfigurasiPembimbingAkademiks\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use App\Enums\PembimbingAkademikMode;
use App\Models\KonfigurasiPembimbingAkademik;
use App\Models\RefAngkatan;
use App\Services\PembimbingAkademikService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class KonfigurasiPembimbingAkademikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konfigurasi Mode Pembimbing')
                ->columnSpanFull()
                ->description('Menentukan bagaimana Dosen Wali ditetapkan untuk satu angkatan pada satu program studi. Konfigurasi ini dibaca otomatis oleh halaman Penugasan Pembimbing — pastikan status Aktif menyala sebelum admin lain menugaskan Dosen Wali untuk kombinasi ini.')
                ->components([
                    Select::make('prodi_id')
                        ->label('Program Studi')
                        ->options(
                            fn(): array => app(FormResolver::class)
                                ->prodiOptions(auth()->user())
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn($rule, $get) =>
                            $rule->where(
                                'angkatan_id',
                                $get('angkatan_id')
                            )
                        )
                        ->validationMessages([
                            'unique' =>
                            'Kombinasi Program Studi dan Angkatan ini sudah memiliki konfigurasi.',
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
                        ->live()
                        ->native(false)
                        ->helperText('Per Kelas: satu dosen wali berlaku untuk satu kelas. Per Mahasiswa: dosen wali ditetapkan satu per satu ke tiap mahasiswa.'),
                    Placeholder::make('dampak_konfigurasi')
                        ->label('')
                        ->columnSpanFull()
                        ->visible(fn(?KonfigurasiPembimbingAkademik $record) => $record !== null)
                        ->content(function (?KonfigurasiPembimbingAkademik $record, $get) {
                            if (! $record) {
                                return '';
                            }

                            $total = app(PembimbingAkademikService::class)
                                ->totalPenugasanAktifUntukKombinasi($get('prodi_id'), $get('angkatan_id'));

                            if ($total === 0) {
                                return new HtmlString(
                                    '<div class="rounded-lg bg-gray-50 dark:bg-gray-500/10 p-3 text-sm text-gray-600 dark:text-gray-400">
                                        Belum ada penugasan Dosen Wali aktif untuk kombinasi ini — aman diubah.
                                    </div>'
                                );
                            }

                            return new HtmlString(
                                '<div class="rounded-lg bg-warning-50 dark:bg-warning-500/10 p-3 text-sm text-warning-700 dark:text-warning-400">
                                    ⚠️ Ada <strong>' . $total . '</strong> penugasan Dosen Wali aktif yang berada di bawah kombinasi ini.
                                    Mengubah mode <strong>TIDAK</strong> mengubah penugasan yang sudah ada — penugasan lama tetap
                                    seperti saat dibuat, hanya penugasan BARU berikutnya yang mengikuti mode ini.
                                </div>'
                            );
                        }),

                    Toggle::make('konfirmasi_perubahan_mode')
                        ->label('Saya paham: penugasan yang sudah ada tidak berubah, hanya penugasan baru yang mengikuti mode ini')
                        ->columnSpanFull()
                        ->dehydrated(false)
                        ->accepted()
                        ->required()
                        ->visible(function (?KonfigurasiPembimbingAkademik $record, $get) {
                            if (! $record) {
                                return false;
                            }

                            $total = app(PembimbingAkademikService::class)
                                ->totalPenugasanAktifUntukKombinasi($get('prodi_id'), $get('angkatan_id'));

                            return $total > 0 && $get('mode') !== $record->mode->value;
                        }),

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
