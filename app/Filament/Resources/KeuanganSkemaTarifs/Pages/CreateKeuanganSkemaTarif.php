<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs\Pages;

use App\Filament\Resources\KeuanganSkemaTarifs\KeuanganSkemaTarifResource;
use App\Models\KeuanganSkemaTarif;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateKeuanganSkemaTarif extends CreateRecord
{
    protected static string $resource = KeuanganSkemaTarifResource::class;

    protected function beforeCreate(): void
    {
        $exists = KeuanganSkemaTarif::query()
            ->where('angkatan_id', $this->data['angkatan_id'])
            ->where('prodi_id', $this->data['prodi_id'])
            ->where('program_kelas_id', $this->data['program_kelas_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Skema tarif sudah tersedia')
                ->body('Kombinasi Angkatan, Program Studi, dan Program Kelas sudah memiliki skema tarif.')
                ->warning()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'program_kelas_id' => 'Skema tarif dengan kombinasi tersebut sudah ada.',
            ]);
        }
    }
}
