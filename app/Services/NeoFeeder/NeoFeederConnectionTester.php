<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

use App\Services\NeoFeeder\Contracts\NeoFeederClientInterface;
use App\Services\NeoFeeder\Exceptions\NeoFeederException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Test koneksi + autentikasi: GetProdi limit 1 offset 0.
 * Data hasilnya TIDAK disimpan; hanya struktur response yang diringkas.
 */
final class NeoFeederConnectionTester
{
    public function __construct(
        private readonly NeoFeederClientInterface $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function test(): NeoFeederConnectionResult
    {
        try {
            $response = $this->client->request('GetProdi', [
                'filter' => '',
                'order' => '',
                'limit' => '1',
                'offset' => '0',
            ]);
        } catch (Throwable $e) {
            if (! $e instanceof NeoFeederException) {
                // Sengaja hanya class + message (bukan objek exception) agar
                // stack trace tidak ikut tertulis ke log.
                $this->logger->error('Neo Feeder: kegagalan tak terduga saat test koneksi.', [
                    'exception' => $e::class,
                    'error' => $e->getMessage(),
                ]);
            }

            // Detail untuk NeoFeederException sudah di-log oleh NeoFeederClient.
            return new NeoFeederConnectionResult(
                NeoFeederConnectionStatus::fromException($e),
                technicalDetail: $e::class . ': ' . $e->getMessage(),
            );
        }

        $data = $response['data'] ?? null;

        return new NeoFeederConnectionResult(
            NeoFeederConnectionStatus::Connected,
            diagnostics: [
                'response_keys' => array_keys($response),
                'error_code' => $response['error_code'],
                'error_desc' => $response['error_desc'] ?? null,
                'data_type' => get_debug_type($data),
                'data_count' => is_array($data) ? count($data) : null,
                'data_first_row_keys' => is_array($data) && isset($data[0]) && is_array($data[0])
                    ? array_keys($data[0])
                    : [],
            ],
        );
    }
}
