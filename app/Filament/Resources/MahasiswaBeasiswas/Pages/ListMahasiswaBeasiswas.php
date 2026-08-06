<?php

namespace App\Filament\Resources\MahasiswaBeasiswas\Pages;

use App\Exports\MahasiswaBeasiswaExport;
use App\Filament\Resources\MahasiswaBeasiswas\MahasiswaBeasiswaResource;
use App\Imports\MahasiswaBeasiswaImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListMahasiswaBeasiswas extends ListRecords
{
    protected static string $resource = MahasiswaBeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->createAnother(false),
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => Excel::download(
                    new MahasiswaBeasiswaExport,
                    'mahasiswa-beasiswa-' . now()->format('Y-m-d') . '.xlsx'
                )),

            // Action Import Excel
            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(new MahasiswaBeasiswaImport, $data['file']);

                        Notification::make()
                            ->title('Import Berhasil')
                            ->body('Data beasiswa mahasiswa berhasil dimasukkan.')
                            ->success()
                            ->send();
                    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                        $failures = $e->failures();
                        $errorMessage = collect($failures)
                            ->map(fn($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                            ->implode('<br>');

                        Notification::make()
                            ->title('Gagal Validasi Import')
                            ->body($errorMessage)
                            ->danger()
                            ->persistent()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
