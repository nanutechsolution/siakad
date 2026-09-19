<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder\Exceptions;

/**
 * Server tidak bisa dihubungi: connection refused, DNS gagal, atau timeout.
 */
final class NeoFeederConnectionException extends NeoFeederException
{
}
