<?php

namespace App\Services\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfDocument;
use App\Models\PdfSignature;
use App\Models\PdfSignatureAuthority;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PdfSigner
{
    /**
     * Resolve daftar penandatangan (READ-ONLY) — dipanggil SEBELUM render,
     * supaya nama & jabatan bisa tampil di dalam dokumen.
     */
    public function resolveSigners(PdfDocumentType $type): array
    {
        $authorities = PdfSignatureAuthority::query()
            ->where('document_type', $type->value)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        if ($authorities->isEmpty()) {
            throw new RuntimeException(
                "Belum ada konfigurasi penandatangan (pdf_signature_authorities) untuk jenis dokumen [{$type->value}]."
            );
        }

        return $authorities->map(function (PdfSignatureAuthority $authority) {
            $pejabat = DB::table('trx_person_jabatan')
                ->join('ref_person', 'ref_person.id', '=', 'trx_person_jabatan.person_id')
                ->where('trx_person_jabatan.jabatan_id', $authority->jabatan_id)
                ->where('trx_person_jabatan.tanggal_mulai', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('trx_person_jabatan.tanggal_selesai')
                        ->orWhere('trx_person_jabatan.tanggal_selesai', '>=', now());
                })
                ->orderByDesc('trx_person_jabatan.tanggal_mulai')
                ->select(['ref_person.id as person_id', 'ref_person.nama_lengkap'])
                ->first();

            if (! $pejabat) {
                throw new RuntimeException(
                    "Tidak ditemukan pejabat aktif untuk jabatan_id [{$authority->jabatan_id}] ({$authority->label}). " .
                        'Dokumen tidak dapat diterbitkan tanpa penandatangan yang sah.'
                );
            }

            return [
                'authorityId' => $authority->id,
                'urutan' => $authority->urutan,
                'label' => $authority->label,
                'personId' => $pejabat->person_id,
                'namaLengkap' => $pejabat->nama_lengkap,
            ];
        })->all();
    }

    /**
     * Simpan riwayat tanda tangan SETELAH dokumen berhasil dirender & disimpan.
     */
    public function persistSignatures(PdfDocument $document, array $resolvedSigners): void
    {
        foreach ($resolvedSigners as $signer) {
            PdfSignature::create([
                'pdf_document_id' => $document->id,
                'signature_authority_id' => $signer['authorityId'],
                'person_id' => $signer['personId'],
                'nama_penandatangan_snapshot' => $signer['namaLengkap'],
                'jabatan_snapshot' => $signer['label'],
                'urutan' => $signer['urutan'],
                'document_hash_at_signing' => $document->file_hash,
                'signed_at' => now(),
                'status' => 'signed',
            ]);
        }
    }
}
