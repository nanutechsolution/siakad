<?php

namespace App\Contracts\Pdf;

interface PdfDataResolverInterface
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function resolve(array $context): PdfDocumentDataInterface;
}
