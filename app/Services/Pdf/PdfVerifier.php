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
            // Perbaikan: Hindari memanggil property langsung pada objek yang mungkin null
            'nomor_dokumen_diminta' => $document?->nomor_dokumen ?? $documentId,
            'ditemukan' => (bool) $document,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);

        if (! $document) {
            return [
                'valid' => false,
                'institutionName' => null,
                'documentType' => null,
                'nomorDokumen' => null,
                'kodeVerifikasi' => null,
                'namaPemilik' => null,
                'nim' => null,
                'programStudi' => null,
                'fakultas' => null,
                'status' => null,
                'tanggalDiterbitkan' => null,
                'signatures' => collect()
            ];
        }

        $signatures = PdfSignature::query()
            ->where('pdf_document_id', $document->id)
            ->orderBy('urutan')
            ->get(['nama_penandatangan_snapshot', 'jabatan_snapshot', 'signed_at']);

        return [
            'valid' => true,
            'institutionName' => $document->institution_name ?? 'Universitas Negeri',
            'documentType' => PdfDocumentType::from($document->document_type)->label(),
            'nomorDokumen' => $document->nomor_dokumen,
            'kodeVerifikasi' => $document->kode_verifikasi ?? $document->id,
            'namaPemilik' => $document->nama_pemilik ?? '-',
            'nim' => $document->nim ?? '-',
            'programStudi' => $document->program_studi ?? '-',
            'fakultas' => $document->fakultas ?? '-',
            'status' => $document->status,
            'tanggalDiterbitkan' => $document->generated_at,
            'signatures' => $signatures,
        ];
    }
}
