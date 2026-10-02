<?php

namespace App\Filament\Actions\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use App\Services\Pdf\PdfService;
use Closure;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Throwable;

class PdfPreviewAction
{
    /**
     * Action pratinjau untuk dokumen Semi-Permanent/Archived:
     * dokumen dibuat (atau diambil dari arsip bila belum berubah), lalu
     * ditampilkan di modal dengan tombol Cetak dan Unduh.
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
        string $icon = 'heroicon-o-printer',
        string $ability = 'view',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color('success')
            ->authorize($ability)
            ->modalHeading($label)
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(function ($record) use ($type, $contextResolver, $documentableResolver, $label): View {
                try {
                    $documentable = $documentableResolver($record);

                    $document = app(PdfService::class)->generateArchived(
                        type: $type,
                        context: $contextResolver($record),
                        documentableType: $documentable['documentableType'],
                        documentableId: $documentable['documentableId'],
                    );

                    return view('filament.pdf.preview', [
                        'judul' => $label,
                        'error' => null,
                        'previewUrl' => route('pdf.dokumen.preview', ['document' => $document->getKey()]),
                        'downloadUrl' => route('pdf.dokumen.download', ['document' => $document->getKey()]),
                    ]);
                } catch (ProcessException $e) {
                    report($e);

                    return self::galat($label, 'Dokumen gagal dibuat oleh mesin PDF. Hubungi BTSI.');
                } catch (RuntimeException $e) {
                    return self::galat($label, $e->getMessage());
                } catch (Throwable $e) {
                    report($e);

                    return self::galat($label, 'Dokumen gagal dibuat. Hubungi BTSI.');
                }
            });
    }

    protected static function galat(string $label, string $pesan): View
    {
        return view('filament.pdf.preview', [
            'judul' => $label,
            'error' => $pesan,
            'previewUrl' => null,
            'downloadUrl' => null,
        ]);
    }
}
