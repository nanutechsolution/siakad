<?php

namespace App\Filament\Actions\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfDocument;
use App\Services\Pdf\PdfService;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use RuntimeException;

class PdfDownloadAction
{
    /**
     * Filament Action untuk mengunduh dokumen PDF Dynamic.
     *
     * @param Closure(mixed $record): array $contextResolver
     */
    public static function make(
        string $name,
        string $label,
        PdfDocumentType $type,
        Closure $contextResolver,
        string $icon = 'heroicon-o-document-arrow-down',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color('success')
            ->url(function ($record) use ($type, $contextResolver) {

                $context = $contextResolver($record);

                return route('pdf.download', [
                    'type' => $type->value,
                    'context' => base64_encode(json_encode($context)),
                ]);
            })
            ->openUrlInNewTab();
    }

    /**
     * Filament Action untuk dokumen Semi-Permanent/Archived.
     *
     * Dokumen ini butuh QR verifikasi & penomoran, jadi wajib disimpan dulu
     * ke `pdf_documents` (generateArchived) baru diunduh — bukan lewat
     * route `pdf.download` yang hanya untuk Dynamic.
     *
     * @param Closure(mixed): array $contextResolver
     * @param Closure(mixed): array{documentableType: class-string, documentableId: string} $documentableResolver
     */
    public static function makeArchived(
        string $name,
        string $label,
        PdfDocumentType $type,
        Closure $contextResolver,
        Closure $documentableResolver,
        string $icon = 'heroicon-o-document-arrow-down',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color('success')
            ->action(function ($record) use ($type, $contextResolver, $documentableResolver) {
                try {
                    $pdfService = app(PdfService::class);

                    $documentable = $documentableResolver($record);

                    $document = $pdfService->generateArchived(
                        type: $type,
                        context: $contextResolver($record),
                        documentableType: $documentable['documentableType'],
                        documentableId: $documentable['documentableId'],
                    );

                    return $pdfService->downloadArchived($document);
                } catch (RuntimeException $e) {
                    Notification::make()
                        ->title('Dokumen belum dapat dicetak')
                        ->body($e->getMessage())
                        ->warning()
                        ->duration(7000)
                        ->send();

                    return null;
                }
            });
    }
}
