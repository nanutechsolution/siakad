<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use Filament\Resources\Pages\Page;

class PilihKonteks extends Page
{
    protected static string $resource = KelasResource::class;

    protected string $view = 'filament.resources.kelas.pages.pilih-konteks';
}
