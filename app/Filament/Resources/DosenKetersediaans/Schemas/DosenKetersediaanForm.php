<?php

namespace App\Filament\Resources\DosenKetersediaans\Schemas;

use App\Models\DosenKetersediaan;
use App\Models\TrxDosen;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class DosenKetersediaanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dosen_id')
                    ->label('Dosen')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    // Label memakai accessor `nama_dengan_gelar` (RefPerson),
                    // bukan kolom asli, jadi pakai getSearchResultsUsing /
                    // getOptionLabelUsing manual, bukan ->relationship().
                    ->getSearchResultsUsing(function (string $search): array {
                        return TrxDosen::query()
                            ->with('person.gelars')
                            ->whereHas('person', fn($query) => $query->where('nama_lengkap', 'like', "%{$search}%"))
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn(TrxDosen $dosen) => [
                                $dosen->getKey() => $dosen->person?->nama_dengan_gelar ?? $dosen->getKey(),
                            ])
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        return TrxDosen::query()
                            ->with('person.gelars')
                            ->find($value)
                            ?->person
                            ?->nama_dengan_gelar;
                    })
                    // Preload: tampilkan sebagian dosen aktif secara default
                    // sebelum admin mengetik apa pun.
                    ->options(function (): array {
                        return TrxDosen::query()
                            ->with('person.gelars')
                            ->where('is_active', true)
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn(TrxDosen $dosen) => [
                                $dosen->getKey() => $dosen->person?->nama_dengan_gelar ?? $dosen->getKey(),
                            ])
                            ->all();
                    }),

                Select::make('hari')
                    ->label('Hari')
                    ->native(false)
                    ->required()
                    ->options(array_combine(DosenKetersediaan::HARI_URUTAN, DosenKetersediaan::HARI_URUTAN)),

                TimePicker::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->native(false)
                    ->seconds(false)
                    ->required(),

                TimePicker::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->native(false)
                    ->seconds(false)
                    ->required()
                    ->after('jam_mulai')
                    ->validationMessages([
                        'after' => 'Jam Selesai harus lebih besar dari Jam Mulai.',
                    ])
                    // Validasi bentrok server-side: berlaku saat create & edit.
                    ->rule(function (Get $get, ?Model $record): Closure {
                        return function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            $dosenId = $get('dosen_id');
                            $hari = $get('hari');
                            $jamMulai = $get('jam_mulai');
                            $jamSelesai = $value;

                            if (blank($dosenId) || blank($hari) || blank($jamMulai) || blank($jamSelesai)) {
                                return;
                            }

                            $bentrok = DosenKetersediaan::findBentrok(
                                dosenId: $dosenId,
                                hari: $hari,
                                jamMulai: $jamMulai,
                                jamSelesai: $jamSelesai,
                                kecualiId: $record?->getKey(),
                            );

                            if ($bentrok) {
                                $mulai = substr((string) $bentrok->jam_mulai, 0, 5);
                                $selesai = substr((string) $bentrok->jam_selesai, 0, 5);

                                $fail("Jadwal ketersediaan dosen bentrok dengan {$bentrok->hari} {$mulai}–{$selesai}.");
                            }
                        };
                    }),
            ]);
    }
}
