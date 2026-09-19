<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder\Contracts;

use App\Services\NeoFeeder\Exceptions\NeoFeederException;

interface NeoFeederClientInterface
{
    /**
     * Kirim satu request ber-token ke Neo Feeder (POST live2.php).
     *
     * `act` dan `token` diisi oleh client; $params berisi field lain
     * seperti filter, order, limit, offset.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed> response JSON yang sudah dipastikan error_code = 0
     *
     * @throws NeoFeederException
     */
    public function request(string $act, array $params = []): array;

    /**
     * Login ke Neo Feeder (act=GetToken) memakai username/password pada
     * konfigurasi, lalu kembalikan tokennya. Client TIDAK menyimpan token.
     *
     * @throws NeoFeederException
     */
    public function fetchToken(): string;
}
