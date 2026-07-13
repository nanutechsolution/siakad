<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('kampus.nama', 'Universitas Contoh');
        $this->migrator->add('kampus.nama_singkat', 'UNCON');
        $this->migrator->add('kampus.alamat', 'Jl. Pendidikan No. 1, Kota Contoh 12345');
        $this->migrator->add('kampus.telepon', '(0361) 000000');
        $this->migrator->add('kampus.email', 'info@kampus.ac.id');
        $this->migrator->add('kampus.website', 'www.kampus.ac.id');
        $this->migrator->add('kampus.akreditasi', null);
        $this->migrator->add('kampus.logo_path', null); // path relatif di storage
    }
};
