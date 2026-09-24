<?php

namespace App\Filament\Resources\Mahasiswas\Pages;

use App\Filament\Resources\Mahasiswas\MahasiswaResource;
use App\Filament\Resources\Mahasiswas\Pages\Concerns\HasMahasiswaMutasiProdiAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditMahasiswa extends EditRecord
{
    use HasMahasiswaMutasiProdiAction;

    protected static string $resource = MahasiswaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            $this->makeMutasiProdiAction(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            isset($data['prodi_id'])
            && $this->getRecord()->prodi_id !== null
            && (int) $data['prodi_id'] !== (int) $this->getRecord()->prodi_id
        ) {
            throw ValidationException::withMessages([
                'data.prodi_id' => 'Program Studi tidak bisa diubah langsung. Gunakan aksi Mutasi Prodi.',
            ]);
        }

        return $data;
    }
}
