<?php

namespace App\DTO;

use Illuminate\Support\Collection;

readonly class KrsValidationResult
{
    /**
     * @param Collection<int, ValidationItem> $items
     */
    public function __construct(
        public bool $isEligibleToApprove,
        public Collection $items
    ) {}
}