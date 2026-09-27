<?php

declare(strict_types=1);

namespace App\Services\Wilayah;

use App\Contracts\WilayahSourceInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use RuntimeException;

final class CahyadsnWilayahSource implements WilayahSourceInterface
{
    private ?string $body = null;

    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $url,
        private readonly int $timeout = 60,
    ) {
    }

    public function provinces(): iterable
    {
        yield from $this->rows('provinces');
    }

    public function regencies(): iterable
    {
        yield from $this->rows('regencies');
    }

    public function districts(): iterable
    {
        yield from $this->rows('districts');
    }

    public function villages(): iterable
    {
        yield from $this->rows('villages');
    }

    /**
     * Parse the cahyadsn SQL dump line-by-line. The source's code hierarchy is:
     * province, regency/city, district, village/kelurahan.
     *
     * @return iterable<array<string, string>>
     */
    private function rows(string $level): iterable
    {
        $body = $this->download();
        $pattern = "/\\('((?:''|[^'])*)',\\s*'((?:''|[^'])*)'\\)/";

        foreach (explode("\n", $body) as $line) {
            if (! str_contains($line, "('") || ! preg_match_all($pattern, $line, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $match) {
                $code = trim(str_replace("''", "'", $match[1]));
                $name = trim(str_replace("''", "'", $match[2]));
                $parts = explode('.', $code);

                $row = match (count($parts)) {
                    1 => ['level' => 'provinces', 'code' => $code, 'name' => $name],
                    2 => ['level' => 'regencies', 'code' => $code, 'name' => $name, 'province_code' => $parts[0]],
                    3 => ['level' => 'districts', 'code' => $code, 'name' => $name, 'regency_code' => $parts[0] . '.' . $parts[1]],
                    4 => ['level' => 'villages', 'code' => $code, 'name' => $name, 'type' => str_starts_with($parts[3], '1') ? 'kelurahan' : 'desa', 'district_code' => $parts[0] . '.' . $parts[1] . '.' . $parts[2]],
                    default => null,
                };

                if ($row !== null && $row['level'] === $level) {
                    unset($row['level']);
                    yield $row;
                }
            }
        }
    }

    private function download(): string
    {
        if ($this->body !== null) {
            return $this->body;
        }

        $response = $this->http
            ->timeout($this->timeout)
            ->connectTimeout($this->timeout)
            ->get($this->url);

        if (! $response->successful()) {
            throw new RuntimeException('Wilayah source returned HTTP ' . $response->status() . '.');
        }

        $body = $response->body();
        if (! str_contains($body, 'CREATE TABLE') || ! str_contains($body, 'INSERT INTO')) {
            throw new RuntimeException('Wilayah source is not a recognized SQL dump.');
        }

        return $this->body = $body;
    }
}
