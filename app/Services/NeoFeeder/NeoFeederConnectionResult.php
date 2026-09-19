<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

final class NeoFeederConnectionResult
{
    /**
     * @param  array<string, mixed>  $diagnostics    ringkasan struktur response (tanpa isi data)
     * @param  string|null           $technicalDetail  hanya untuk CLI/developer, JANGAN tampilkan di UI
     */
    public function __construct(
        public readonly NeoFeederConnectionStatus $status,
        public readonly array $diagnostics = [],
        public readonly ?string $technicalDetail = null,
    ) {
    }

    public function isConnected(): bool
    {
        return $this->status === NeoFeederConnectionStatus::Connected;
    }

    public function message(): string
    {
        return $this->status->message();
    }
}
