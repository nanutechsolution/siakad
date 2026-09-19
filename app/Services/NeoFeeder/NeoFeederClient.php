<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

use App\Services\NeoFeeder\Contracts\NeoFeederClientInterface;
use App\Services\NeoFeeder\Exceptions\NeoFeederApiException;
use App\Services\NeoFeeder\Exceptions\NeoFeederConnectionException;
use App\Services\NeoFeeder\Exceptions\NeoFeederException;
use App\Services\NeoFeeder\Exceptions\NeoFeederHttpException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Psr\Log\LoggerInterface;

/**
 * Hanya bertanggung jawab atas komunikasi HTTP ke Neo Feeder.
 *
 * Catatan keamanan: payload (berisi token / password) TIDAK PERNAH di-log,
 * dan body response yang di-log selalu disensor + dipotong.
 */
final class NeoFeederClient implements NeoFeederClientInterface
{
    private const LOG_BODY_LIMIT = 500;

    public function __construct(
        private readonly NeoFeederConfig $config,
        private readonly HttpFactory $http,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function request(string $act, array $params = []): array
    {
        $this->config->assertHasEndpoint();
        $this->config->assertHasToken();

        // act & token selalu dari client; key yang sama di $params diabaikan.
        $payload = ['act' => $act, 'token' => $this->config->token] + $params;

        return $this->send($act, $payload);
    }

    public function fetchToken(): string
    {
        $this->config->assertHasEndpoint();
        $this->config->assertHasCredentials();

        $response = $this->send('GetToken', [
            'act' => 'GetToken',
            'username' => $this->config->username,
            'password' => $this->config->password,
        ]);

        $data = $response['data'] ?? null;
        $token = is_array($data) ? ($data['token'] ?? null) : null;
        $token ??= $response['token'] ?? null;

        if (! is_string($token) || trim($token) === '') {
            $this->logger->error('Neo Feeder: GetToken berhasil tetapi token tidak ditemukan pada response.', [
                'response_keys' => array_keys($response),
                'data_keys' => is_array($data) ? array_keys($data) : [],
            ]);

            throw new NeoFeederException('Response GetToken tidak berisi token.');
        }

        return trim($token);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function send(string $act, array $payload): array
    {
        $context = ['act' => $act, 'url' => $this->config->url];
        $startedAt = hrtime(true);

        try {
            $response = $this->http
                ->asJson()
                ->acceptJson()
                ->timeout($this->config->timeout)
                ->connectTimeout($this->config->connectTimeout)
                ->withOptions(['verify' => $this->config->verifySsl])
                ->post($this->config->url, $payload);
        } catch (ConnectionException $e) {
            $message = $e->getMessage();
            $isTimeout = str_contains($message, 'cURL error 28')
                || str_contains(strtolower($message), 'timed out');

            $this->logger->error('Neo Feeder: koneksi gagal.', $context + [
                'timeout' => $isTimeout,
                'duration_ms' => $this->elapsedMs($startedAt),
                'error' => $message,
            ]);

            throw new NeoFeederConnectionException(
                $isTimeout
                    ? 'Koneksi ke Neo Feeder timeout.'
                    : 'Tidak dapat terhubung ke Neo Feeder.',
                0,
                $e,
            );
        }

        $context += [
            'http_status' => $response->status(),
            'duration_ms' => $this->elapsedMs($startedAt),
        ];

        if (! $response->successful()) {
            $this->logger->error('Neo Feeder: HTTP error.', $context + [
                'body' => $this->excerpt($response->body()),
            ]);

            throw new NeoFeederHttpException($response->status());
        }

        $data = $response->json();

        if (! is_array($data)) {
            $this->logger->error('Neo Feeder: response bukan JSON object/array.', $context + [
                'body' => $this->excerpt($response->body()),
            ]);

            throw new NeoFeederException('Response Neo Feeder bukan JSON yang valid.');
        }

        if (! array_key_exists('error_code', $data) || ! is_numeric($data['error_code'])) {
            $this->logger->error('Neo Feeder: field error_code tidak ditemukan/tidak valid.', $context + [
                'response_keys' => array_keys($data),
                'body' => $this->excerpt($response->body()),
            ]);

            throw new NeoFeederException('Format response Neo Feeder tidak dikenali (error_code tidak ada).');
        }

        $errorCode = (int) $data['error_code'];

        if ($errorCode !== 0) {
            $errorDesc = (string) ($data['error_desc'] ?? '');

            $this->logger->warning('Neo Feeder: response menunjukkan error.', $context + [
                'error_code' => $errorCode,
                'error_desc' => $errorDesc,
            ]);

            throw new NeoFeederApiException($errorCode, $errorDesc);
        }

        // Body sengaja tidak di-log pada jalur sukses (bisa berisi token).
        $this->logger->info('Neo Feeder: request berhasil.', $context + ['error_code' => 0]);

        return $data;
    }

    private function elapsedMs(int $startedAt): int
    {
        return (int) round((hrtime(true) - $startedAt) / 1_000_000);
    }

    private function excerpt(string $body): string
    {
        foreach ([$this->config->token, $this->config->password] as $secret) {
            if ($secret !== '') {
                $body = str_replace($secret, '[REDACTED]', $body);
            }
        }

        return mb_substr($body, 0, self::LOG_BODY_LIMIT);
    }
}
