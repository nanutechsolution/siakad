<?php

namespace App\Http\Controllers;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\Krs;
use App\Models\PdfDocument;
use App\Services\Pdf\PdfService;
use App\Services\Pdf\PdfStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Melayani dokumen PDF arsip (SEMI_PERMANENT/ARCHIVED) untuk pratinjau dan unduhan.
 * Otorisasi mengikuti jenis dokumen; jenis yang belum didaftarkan di
 * authorizeDocument() otomatis ditolak (403).
 */
class PdfDocumentController
{
    public function preview(Request $request, PdfDocument $document, PdfStorage $storage): Response
    {
        $this->authorizeDocument($request, $document);

        try {
            $binary = $storage->get($document->file_disk, $document->file_path);
        } catch (Throwable $e) {
            report($e);
            abort(404, 'Berkas dokumen tidak ditemukan di penyimpanan.');
        }

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($document->file_path) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download(Request $request, PdfDocument $document, PdfService $service): Response
    {
        $this->authorizeDocument($request, $document);

        return $service->downloadArchived($document);
    }

    protected function authorizeDocument(Request $request, PdfDocument $document): void
    {
        $user = $request->user();

        abort_unless($user, 401);

        $type = $document->document_type instanceof PdfDocumentType
            ? $document->document_type
            : PdfDocumentType::tryFrom((string) $document->document_type);

        $context = is_array($document->metadata) ? ($document->metadata['context'] ?? []) : [];

        $diizinkan = match ($type) {
            PdfDocumentType::KRS => $this->bolehMelihatKrs($user, is_array($context) ? $context : []),
            default => false,
        };

        abort_unless($diizinkan, 403, 'Anda tidak berwenang mengakses dokumen ini.');
    }

    protected function bolehMelihatKrs($user, array $context): bool
    {
        $krsId = $context['krs_id'] ?? null;

        if (blank($krsId)) {
            return false;
        }

        $krs = Krs::query()->find($krsId);

        if (! $krs) {
            return false;
        }

        if (Gate::forUser($user)->allows('view', $krs)) {
            return true;
        }

        // Mahasiswa pemilik KRS.
        return ! empty($user->mahasiswa_id)
            && (string) $user->mahasiswa_id === (string) $krs->mahasiswa_id;
    }
}
