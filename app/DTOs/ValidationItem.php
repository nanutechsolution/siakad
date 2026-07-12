<?php

namespace App\DTO;

class ValidationItem
{
    public function __construct(
        public string $rulesName,
        public bool $isValid,
        public ?string $errorMessage = null
    ) {}
}