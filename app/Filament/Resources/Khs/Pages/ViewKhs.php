<?php

namespace App\Filament\Resources\Khs\Pages;

use App\Enums\Pdf\PdfDocumentType;
use App\Filament\Resources\Khs\KhsResource;
use App\Models\Mahasiswa;
use App\Services\Pdf\PdfService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use RuntimeException;

class ViewKhs extends ViewRecord
{
    protected static string $resource = KhsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_khs')
                ->label('Cetak KHS')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    try {
                        $khs = $this->record;

                        $pdfService = app(PdfService::class);

                        $document = $pdfService->generateArchived(
                            type: PdfDocumentType::KHS,
                            context: [
                                'krs_id' => $khs->id,
                                'mahasiswa_id' => $khs->mahasiswa_id,
                                'tahun_akademik_id' => $khs->tahun_akademik_id,
                            ],
                            documentableType: Mahasiswa::class,
                            documentableId: $khs->mahasiswa_id,
                        );

                        return $pdfService->downloadArchived($document);
                    } catch (RuntimeException $e) {
                        Notification::make()
                            ->title('KHS belum dapat dicetak')
                            ->body($e->getMessage())
                            ->warning()
                            ->duration(7000)
                            ->send();

                        return null;
                    }
                }),
        ];
    }
}
