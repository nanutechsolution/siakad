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

    public static function group(): string
    {
        return 'kampus';
    }
}