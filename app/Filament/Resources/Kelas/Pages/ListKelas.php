<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('plotting_mahasiswa')
                ->label('Plotting Mahasiswa')
                ->icon('heroicon-o-user-plus')
                ->color('gray')
                ->url(fn() => KelasResource::getUrl('plotting', $this->konteksAktif()))
                ->disabled(fn() => blank($this->konteksAktif()['program_id'] ?? null)
                    || blank($this->konteksAktif()['angkatan_id'] ?? null))
                ->tooltip(fn() => $this->konteksAktif()['program_id'] ?? null
                    ? null
                    : 'Pilih Program dan Angkatan terlebih dahulu'),
        ];
    }

    protected function konteksAktif(): array
    {
        // Meneruskan konteks yang sedang aktif di ListKelas (dari query string)
        // ke halaman Plotting, supaya operator tidak perlu memilih ulang.
        return array_filter([
            'tahun_akademik_id' => request()->query('tahun_akademik_id'),
            'prodi_id'          => request()->query('prodi_id'),
            'program_id'        => request()->query('program_id'),
            'angkatan_id'       => request()->query('angkatan_id'),
        ]);
    }
}
