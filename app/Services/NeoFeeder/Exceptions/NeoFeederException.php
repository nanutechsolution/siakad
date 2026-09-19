<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder\Exceptions;

use RuntimeException;

/**
 * Exception dasar untuk semua kegagalan komunikasi dengan Neo Feeder.
 * Dipakai langsung untuk response yang tidak dikenali.
 */
class NeoFeederException extends RuntimeException
{
}
