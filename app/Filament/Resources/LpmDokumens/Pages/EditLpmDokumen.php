<?php

namespace App\Filament\Resources\LpmDokumens\Pages;

use App\Filament\Resources\LpmDokumens\LpmDokumenResource;
use App\Models\LpmDokumenRiwayat;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class EditLpmDokumen extends EditRecord
{
    protected static string $resource = LpmDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('naikkanVersi')
                ->label('Naikkan Versi')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('warning')
                ->schema([
                    TextInput::make('versi_baru')
                        ->label('Versi Baru')
                        ->required(),
                    FileUpload::make('file_path')
                        ->label('File Versi Baru')
                        ->directory('lpm/dokumen')
                        ->required(),
                    Textarea::make('changelog')
                        ->label('Ringkasan Perubahan')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    /** @var \App\Models\LpmDokumen $record */
                    $record = $this->record;
                    LpmDokumenRiwayat::create([
                        'dokumen_id' => $record->id,
                        'versi_lama' => $record->versi,
                        'versi_baru' => $data['versi_baru'],
                        'file_path' => $data['file_path'],
                        'changelog' => $data['changelog'] ?? null,
                        'diubah_oleh_person_id' => auth()->user()?->person_id,
                        'tanggal' => now(),
                    ]);

                    // Versi baru wajib melalui alur approval lagi, sehingga
                    // status dikembalikan ke DRAFT (bukan otomatis PUBLISHED).
                    $record->update([
                        'versi' => $data['versi_baru'],
                        'file_path' => $data['file_path'],
                        'status' => 'DRAFT',
                    ]);

                    $this->refreshFormData(['versi', 'file_path', 'status']);
                }),
            DeleteAction::make(),
        ];
    }
}
