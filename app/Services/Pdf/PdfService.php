<?php

namespace App\Services\Pdf;

use App\Contracts\Pdf\PdfDocumentDataInterface;
use App\Enums\Pdf\PdfClassification;
use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfService
{
    public function __construct(
        protected PdfDocumentTypeRegistry $registry,
        protected PdfTemplateEngine $templateEngine,
        protected PdfStorage $storage,
    ) {}

    // /**
    //  * Stream dokumen Dynamic langsung ke browser tanpa disimpan.
    //  */
    // public function stream(PdfDocumentType $type, array $context, ?string $filename = null): Response
    // {
    //     $definition = $this->registry->get($type);
    //     $this->assertClassification($type, $definition, PdfClassification::DYNAMIC);

    //     $dto = $this->resolve($type, $context);
    //     $pdf = $this->templateEngine->render($definition['view'], $dto->toArray(), $definition);

    //     $this->logGenerated($type, $context);

    //     return $pdf->stream($filename ?? $this->buildFilename($type, $dto));
    // }

    // public function download(PdfDocumentType $type, array $context, ?string $filename = null): Response
    // {
    //     $definition = $this->registry->get($type);
    //     $this->assertClassification($type, $definition, PdfClassification::DYNAMIC);

    //     $dto = $this->resolve($type, $context);
    //     $pdf = $this->templateEngine->render($definition['view'], $dto->toArray(), $definition);

    //     $this->logGenerated($type, $context);

    //     return $pdf->download($filename ?? $this->buildFilename($type, $dto));
    // }

    /**
     * Stream dokumen Dynamic langsung ke browser tanpa disimpan.
     */
    public function stream(PdfDocumentType $type, array $context, ?string $filename = null): StreamedResponse
    {
        $definition = $this->registry->get($type);
        $this->assertClassification($type, $definition, PdfClassification::DYNAMIC);

        $dto = $this->resolve($type, $context);
        $pdf = $this->templateEngine->render($definition['view'], $dto->toArray(), $definition);

        $this->logGenerated($type, $context);

        $safeFilename = Str::ascii($filename ?? $this->buildFilename($type, $dto));

        // WAJIB STREAM UNTUK LIVEWIRE
        return response()->stream(
            fn() => print($pdf->output()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $safeFilename . '"'
            ]
        );
    }

    /**
     * Download dokumen Dynamic.
     */
    public function download(PdfDocumentType $type, array $context, ?string $filename = null): StreamedResponse
    {
        $definition = $this->registry->get($type);
        $this->assertClassification($type, $definition, PdfClassification::DYNAMIC);

        $dto = $this->resolve($type, $context);
        $pdf = $this->templateEngine->render($definition['view'], $dto->toArray(), $definition);

        $this->logGenerated($type, $context);

        $safeFilename = Str::ascii($filename ?? $this->buildFilename($type, $dto));

        // WAJIB STREAM DOWNLOAD UNTUK LIVEWIRE
        return response()->streamDownload(
            fn() => print($pdf->output()),
            $safeFilename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Generate (atau ambil versi tersimpan bila belum berubah) untuk dokumen
     * Semi-Permanent/Archived, lalu simpan ke pdf_documents.
     */
    public function generateArchived(
        PdfDocumentType $type,
        array $context,
        string $documentableType,
        string $documentableId,
    ): PdfDocument {
        $definition = $this->registry->get($type);
        if ($definition['classification'] === PdfClassification::DYNAMIC) {
            throw new RuntimeException(
                "Jenis dokumen [{$type->value}] berklasifikasi DYNAMIC dan tidak boleh diarsipkan. Gunakan stream()/download()."
            );
        }

        $dto = $this->resolve($type, $context);
        $fingerprint = $dto->fingerprint();

        /** @var PdfDocument|null $current */
        $current = PdfDocument::query()
            ->where('document_type', $type->value)
            ->where('documentable_type', $documentableType)
            ->where('documentable_id', $documentableId)
            ->where('is_current', true)
            ->first();

        if ($current && ($current->metadata['fingerprint'] ?? null) === $fingerprint) {
            return $current;
        }

        // ID dokumen di-generate lebih dulu supaya bisa dipakai sebagai target
        // link QR SEBELUM PDF dirender (QR harus muncul di dalam isi dokumen).
        $documentId = (string) Str::uuid();

        $viewData = $dto->toArray();
        $nomorDokumen = null;
        $resolvedSigners = [];

        if ($definition['requires_number'] ?? false) {
            $nomorDokumen = app(PdfNumberGenerator::class)->generate($type, $context['kode_unit'] ?? null);
            $viewData['nomorDokumen'] = $nomorDokumen;
        }

        if ($definition['requires_signature'] ?? false) {
            $scope = $dto instanceof \App\Contracts\Pdf\HasSignatureScopeInterface ? $dto->signatureScope() : [];
            $resolvedSigners = app(PdfSigner::class)->resolveSigners($type, $scope);
            $viewData['signers'] = $resolvedSigners;
        }
        if ($definition['requires_qr'] ?? false) {
            $viewData['qrCodeBase64'] = app(PdfQrGenerator::class)->generateBase64($documentId);
        }

        $pdf = $this->templateEngine->render($definition['view'], $viewData, $definition);
        $binary = $pdf->output();
        $hash = hash('sha256', $binary);

        $version = $current ? $current->version + 1 : 1;
        $filename = $this->buildFilename($type, $dto, $version);
        $path = $this->storage->put($type, $documentableId, $filename, $binary);

        if ($current) {
            $current->update(['is_current' => false]);
        }

        $document = PdfDocument::create([
            'id' => $documentId,
            'document_type' => $type->value,
            'classification' => $definition['classification']->value,
            'documentable_type' => $documentableType,
            'documentable_id' => $documentableId,
            'nomor_dokumen' => $nomorDokumen,
            'file_disk' => $this->storage->disk(),
            'file_path' => $path,
            'file_hash' => $hash,
            'version' => $version,
            'is_current' => true,
            'status' => $definition['classification'] === PdfClassification::ARCHIVED ? 'final' : 'draft',
            'metadata' => [
                'fingerprint' => $fingerprint,
                'context' => $context,
            ],
            'generated_by' => Auth::id(),
            'generated_at' => now(),
        ]);

        if (! empty($resolvedSigners)) {
            app(PdfSigner::class)->persistSignatures($document, $resolvedSigners);
        }

        $this->logGenerated($type, $context, $document);

        return $document;
    }

    public function downloadArchived(PdfDocument $document, ?string $filename = null): StreamedResponse
    {
        $binary = $this->storage->get($document->file_disk, $document->file_path);
        $filename ??= basename($document->file_path);

        return response()->streamDownload(
            fn() => print($binary),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected function assertClassification(PdfDocumentType $type, array $definition, PdfClassification $expected): void
    {
        if ($definition['classification'] !== $expected) {
            throw new RuntimeException(
                "Jenis dokumen [{$type->value}] bukan klasifikasi {$expected->value}."
            );
        }
    }

    protected function resolve(PdfDocumentType $type, array $context): PdfDocumentDataInterface
    {
        $definition = $this->registry->get($type);

        /** @var \App\Contracts\Pdf\PdfDataResolverInterface $resolver */
        $resolver = app($definition['resolver']);

        return $resolver->resolve($context);
    }

    protected function buildFilename(PdfDocumentType $type, PdfDocumentDataInterface $dto, ?int $version = null): string
    {
        $slug = Str::slug($type->value);
        $suffix = $version ? "_v{$version}" : '';

        return "{$slug}_{$dto->identifier()}{$suffix}_" . now()->format('YmdHis') . '.pdf';
    }

    protected function logGenerated(PdfDocumentType $type, array $context, ?PdfDocument $document = null): void
    {
        if (! function_exists('activity')) {
            return;
        }

        $log = activity('pdf')
            ->causedBy(Auth::user())
            ->withProperties([
                'document_type' => $type->value,
                'context' => $context,
            ]);

        if ($document) {
            $log->performedOn($document);
        }

        $log->log('generate');
    }
}
