<?php

namespace App\Contracts\Pdf;

interface PdfDocumentDataInterface
{
    /** Data yang di-bind ke Blade view. */
    public function toArray(): array;

    /** Identifier pendek untuk nama file (mis. NIM). */
    public function identifier(): string;

    /** Hash sumber data — dipakai untuk deteksi perubahan pada dokumen Semi-Permanent/Archived. */
    public function fingerprint(): string;
}
