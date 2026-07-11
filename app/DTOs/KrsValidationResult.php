<?php

declare(strict_types=1);

namespace App\DTOs;

class KrsValidationResult
{
    public function __construct(
        public readonly bool $passed,
        public readonly string $gateCode,
        public readonly string $message
    ) {}

    public static function pass(string $gateCode, string $message = 'OK'): self
    {
        return new self(true, $gateCode, $message);
    }

    public static function fail(string $gateCode, string $message): self
    {
        return new self(false, $gateCode, $message);
    }
}
