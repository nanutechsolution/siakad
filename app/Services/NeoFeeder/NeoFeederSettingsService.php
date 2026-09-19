<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

use App\Models\NeoFeederSetting;

/**
 * Sumber pengaturan Neo Feeder: database lebih dulu, .env (config/services.php)
 * sebagai cadangan. Password & token tersimpan terenkripsi (cast `encrypted`).
 *
 * Class ini sengaja TIDAK bergantung pada NeoFeederClient agar tidak terjadi
 * dependensi melingkar (client -> config -> settings service).
 */
final class NeoFeederSettingsService
{
    public function current(): NeoFeederSetting
    {
        return NeoFeederSetting::query()->first() ?? new NeoFeederSetting();
    }

    /**
     * Pengaturan efektif untuk NeoFeederConfig::fromArray().
     *
     * @return array<string, mixed>
     */
    public function configArray(): array
    {
        /** @var array<string, mixed> $env */
        $env = (array) config('services.neo_feeder', []);
        $setting = $this->current();

        return [
            'url' => $this->pick($setting->url, $env['url'] ?? null),
            'username' => $this->pick($setting->username, $env['username'] ?? null),
            'password' => $this->pick($setting->password, $env['password'] ?? null),
            'token' => $this->pick($setting->token, $env['token'] ?? null),
            'timeout' => (int) ($setting->timeout ?? $env['timeout'] ?? 60),
            'connect_timeout' => (int) ($setting->connect_timeout ?? $env['connect_timeout'] ?? 10),
            'verify_ssl' => (bool) ($setting->verify_ssl ?? $env['verify_ssl'] ?? true),
        ];
    }

    /**
     * Data untuk mengisi form. Password & token TIDAK PERNAH dikirim ke browser.
     *
     * @return array<string, mixed>
     */
    public function formData(): array
    {
        $config = $this->configArray();

        return [
            'url' => $config['url'],
            'username' => $config['username'],
            'password' => null,
            'verify_ssl' => $config['verify_ssl'],
            'timeout' => $config['timeout'],
            'connect_timeout' => $config['connect_timeout'],
        ];
    }

    public function hasPassword(): bool
    {
        $password = $this->configArray()['password'];

        return $password !== null && $password !== '';
    }

    /**
     * @param  array<string, mixed>  $data  state form
     */
    public function save(array $data): NeoFeederSetting
    {
        $setting = $this->current();

        $setting->url = trim((string) ($data['url'] ?? ''));
        $setting->username = trim((string) ($data['username'] ?? ''));

        $password = (string) ($data['password'] ?? '');
        if ($password !== '') {
            $setting->password = $password;
        }

        $setting->verify_ssl = (bool) ($data['verify_ssl'] ?? true);
        $setting->timeout = max(1, (int) ($data['timeout'] ?? 60));
        $setting->connect_timeout = max(1, (int) ($data['connect_timeout'] ?? 10));

        // URL / kredensial berubah -> token lama tidak berlaku lagi.
        if ($setting->isDirty(['url', 'username', 'password'])) {
            $setting->token = null;
            $setting->token_obtained_at = null;
        }

        $setting->save();

        return $setting;
    }

    public function storeToken(string $token): void
    {
        $setting = $this->current();
        $setting->token = $token;
        $setting->token_obtained_at = now();
        $setting->save();
    }

    public function tokenStatus(): string
    {
        $setting = $this->current();

        if (filled($setting->token)) {
            return $setting->token_obtained_at !== null
                ? 'Token tersimpan (diambil ' . $setting->token_obtained_at->translatedFormat('d F Y H:i') . ').'
                : 'Token tersimpan.';
        }

        if (filled(config('services.neo_feeder.token'))) {
            return 'Memakai token dari .env.';
        }

        return 'Belum ada token. Simpan pengaturan, lalu klik "Ambil Token".';
    }

    private function pick(?string $fromDatabase, mixed $fromEnv): ?string
    {
        if ($fromDatabase !== null && $fromDatabase !== '') {
            return $fromDatabase;
        }

        return ($fromEnv === null || $fromEnv === '') ? null : (string) $fromEnv;
    }
}
