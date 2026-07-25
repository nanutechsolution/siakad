<?php

namespace App\Services\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use Illuminate\Support\Facades\Storage;

class PdfStorage
{
    protected string $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? config('pdf.archive_disk', 'local');
    }

    public function disk(): string
    {
        return $this->disk;
    }

    public function put(PdfDocumentType $type, string $documentableId, string $filename, string $binary): string
    {
        $path = sprintf(
            'pdf-archive/%s/%s/%s/%s',
            $type->value,
            now()->format('Y/m'),
            $documentableId,
            $filename,
        );

        Storage::disk($this->disk)->put($path, $binary);

        return $path;
    }

    public function get(string $disk, string $path): string
    {
        return Storage::disk($disk)->get($path);
    }

    public function exists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    public function delete(string $disk, string $path): bool
    {
        return Storage::disk($disk)->delete($path);
    }
}
