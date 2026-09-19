<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder\Exceptions;

/**
 * Neo Feeder membalas dengan HTTP status non-2xx.
 */
final class NeoFeederHttpException extends NeoFeederException
{
    public function __construct(public readonly int $statusCode)
    {
        parent::__construct(sprintf('Neo Feeder mengembalikan HTTP %d.', $statusCode));
    }

    public function isAuthRelated(): bool
    {
        return in_array($this->statusCode, [401, 403], true);
    }
}
