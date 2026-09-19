<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder\Exceptions;

/**
 * HTTP 2xx, tetapi isi response Neo Feeder menyatakan error (error_code != 0).
 */
final class NeoFeederApiException extends NeoFeederException
{
    public function __construct(
        public readonly int $errorCode,
        public readonly string $errorDesc,
    ) {
        parent::__construct(sprintf(
            'Neo Feeder mengembalikan error_code %d: %s',
            $errorCode,
            $errorDesc,
        ));
    }
}
