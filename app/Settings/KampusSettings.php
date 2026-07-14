<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class KampusSettings extends Settings
{
    public string $nama;
    public string $nama_singkat;
    public string $alamat;
    public string $telepon;
    public string $email;
    public string $website;
    public ?string $akreditasi;
    public ?string $logo_path;
    public bool $reset_nim_tahunan;
    public string $neo_feeder_url;
    public string $neo_feeder_username;
    public string $neo_feeder_password;
    public bool $maintenance_mode;        // Untuk mematikan akses mahasiswa saat update data
    public string $semester_aktif;        // Misal: "2026/2027 Ganjil"
    public int $batas_maksimal_sks;       // Global limit SKS
    public bool $enable_sso_login;        // Untuk integrasi login via email kampus
    public string $smtp_host;
    public static function group(): string
    {
        return 'kampus';
    }
}
