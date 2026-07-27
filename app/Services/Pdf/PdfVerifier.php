<?php

namespace App\Services\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfDocument;
use App\Models\PdfSignature;
use App\Models\PdfVerification;

class PdfVerifier
{
    public function verify(string $documentId, ?string $ip, ?string $userAgent): array
    {
        $document = PdfDocument::query()
            ->where('id', $documentId)
            ->where('status', '!=', 'revoked')
            ->first();

        PdfVerification::create([
            'pdf_document_id' => $document?->id,
            'nomor_dokumen_diminta' => $document->nomor_dokumen ?? $documentId,
            'ditemukan' => (bool) $document,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);

        if (! $document) {
            return ['valid' => false, 'documentType' => null, 'nomorDokumen' => null, 'status' => null, 'generatedAt' => null, 'signatures' => collect()];
        }

        $signatures = PdfSignature::query()
            ->where('pdf_document_id', $document->id)
            ->orderBy('urutan')
            ->get(['nama_penandatangan_snapshot', 'jabatan_snapshot', 'signed_at']);

        return [
            'valid' => true,
            'documentType' => PdfDocumentType::from($document->document_type)->label(),
            'nomorDokumen' => $document->nomor_dokumen,
            'status' => $document->status,
            'generatedAt' => $document->generated_at,
            'signatures' => $signatures,
        ];
    }
}
