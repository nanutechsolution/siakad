<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

use App\Services\NeoFeeder\Contracts\NeoFeederClientInterface;
use App\Services\NeoFeeder\Exceptions\NeoFeederException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Mengambil token baru lewat GetToken dan menyimpannya (terenkripsi).
 * Client tetap hanya mengurus HTTP; penyimpanan ada di sini.
 */
final class NeoFeederAuthenticator
{
    public function __construct(
        private readonly NeoFeederClientInterface $client,
        private readonly NeoFeederSettingsService $settings,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function refreshToken(): NeoFeederConnectionResult
    {
        try {
            $token = $this->client->fetchToken();
            $this->settings->storeToken($token);
        } catch (Throwable $e) {
            if (! $e instanceof NeoFeederException) {
                $this->logger->error('Neo Feeder: kegagalan tak terduga saat mengambil token.', [
                    'exception' => $e::class,
                    'error' => $e->getMessage(),
                ]);
            }

            return new NeoFeederConnectionResult(
                NeoFeederConnectionStatus::fromException($e),
                technicalDetail: $e::class . ': ' . $e->getMessage(),
            );
        }

        $this->logger->info('Neo Feeder: token berhasil diperbarui.');

        return new NeoFeederConnectionResult(NeoFeederConnectionStatus::Connected);
    }
}
