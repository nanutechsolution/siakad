<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

use App\Services\NeoFeeder\Exceptions\NeoFeederConfigurationException;

final class NeoFeederConfig
{
    public function __construct(
        public readonly string $url,
        public readonly string $token = '',
        public readonly int $timeout = 60,
        public readonly int $connectTimeout = 10,
        public readonly bool $verifySsl = true,
        public readonly string $username = '',
        public readonly string $password = '',
    ) {
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            url: trim((string) ($config['url'] ?? '')),
            token: trim((string) ($config['token'] ?? '')),
            timeout: max(1, (int) ($config['timeout'] ?? 60)),
            connectTimeout: max(1, (int) ($config['connect_timeout'] ?? 10)),
            verifySsl: filter_var($config['verify_ssl'] ?? true, FILTER_VALIDATE_BOOL),
            username: trim((string) ($config['username'] ?? '')),
            password: (string) ($config['password'] ?? ''),
        );
    }

    /**
     * Validasi dilakukan saat dipakai (bukan saat dibuat) supaya container
     * tidak melempar exception ketika class ini di-resolve.
     *
     * @throws NeoFeederConfigurationException
     */
    public function assertHasEndpoint(): void
    {
        if ($this->url === '' || filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            throw new NeoFeederConfigurationException('URL Neo Feeder belum diisi atau tidak valid.');
        }
    }

    /**
     * @throws NeoFeederConfigurationException
     */
    public function assertHasToken(): void
    {
        if ($this->token === '') {
            throw new NeoFeederConfigurationException('Token Neo Feeder belum ada. Ambil token terlebih dahulu.');
        }
    }

    /**
     * @throws NeoFeederConfigurationException
     */
    public function assertHasCredentials(): void
    {
        if ($this->username === '' || $this->password === '') {
            throw new NeoFeederConfigurationException('Username/password Neo Feeder belum diisi.');
        }
    }
}
