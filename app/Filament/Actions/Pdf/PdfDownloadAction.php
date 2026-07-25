<?php

namespace App\Filament\Actions\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use Closure;
use Filament\Actions\Action;

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
}
