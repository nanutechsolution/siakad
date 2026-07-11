<?php

namespace App\Filament\Resources\Pegawais\Pages;

use App\Imports\Kepegawaian\PegawaiImporter;
use App\Filament\Resources\Pegawais\PegawaiResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListPegawais extends ListRecords
{
    protected static string $resource = PegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(PegawaiImporter::class)
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->chunkSize(250),

            CreateAction::make()
                ->label('Tambah Manual')
                ->icon('heroicon-o-plus'),
        ];
    }
}
