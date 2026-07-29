<?php

namespace App\Contracts\Pdf;

interface HasSignatureScopeInterface
{
    /**
     * @return array{prodi_id?: int, fakultas_id?: int}
     */
    public function signatureScope(): array;
}
