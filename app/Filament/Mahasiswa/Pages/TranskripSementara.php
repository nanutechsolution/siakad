<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Services\Mahasiswa\NilaiAkademikService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

class TranskripSementara extends Page
{
    protected string $view = 'filament.mahasiswa.pages.transkrip-sementara';
    protected static ?string $navigationLabel = 'Transkrip Sementara';
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::NILAI->value;
    protected static ?string $title = 'Transkrip Akademik Sementara';
    protected static ?int $navigationSort = 3;

      public array $data = [];

       public function mount(NilaiAkademikService $service): void
    {
        $mahasiswa = $service->mahasiswaLogin();
        $this->data = $service->transkripData($mahasiswa);
    }
 
    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->url(route('mahasiswa.transkrip.pdf'))
                ->openUrlInNewTab(),
        ];
    }
}
