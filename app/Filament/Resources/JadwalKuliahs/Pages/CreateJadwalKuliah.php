<?php

namespace App\Filament\Resources\JadwalKuliahs\Pages;

use App\Filament\Resources\JadwalKuliahs\JadwalKuliahResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateJadwalKuliah extends CreateRecord
{
    protected static string $resource = JadwalKuliahResource::class;
    protected function preserveFormDataWhenCreatingAnother(array $data): array
    {
        return Arr::only($data, [
            'tahun_akademik_id',
            'kurikulum_id',
            'hari',
            'jam_mulai',
            'jam_selesai',
        ]);
    }
}
