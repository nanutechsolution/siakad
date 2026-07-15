<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('kampus.maintenance_mode', false);
        $this->migrator->add('kampus.semester_aktif', '2026/2027 Ganjil');
        $this->migrator->add('kampus.batas_maksimal_sks', 24);
        $this->migrator->add('kampus.enable_sso_login', false);
        $this->migrator->add('kampus.smtp_host', 'mail.unmaris.ac.id');
    }
};
