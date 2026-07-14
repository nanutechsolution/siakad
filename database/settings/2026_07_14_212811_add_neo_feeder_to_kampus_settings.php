<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // URL default biasanya menggunakan port 8100 untuk Neo Feeder lokal
        $this->migrator->add('kampus.neo_feeder_url', 'http://localhost:8100');
        $this->migrator->add('kampus.neo_feeder_username', '');
        $this->migrator->add('kampus.neo_feeder_password', '');
    }
    
    public function down(): void
    {
        $this->migrator->delete('kampus.neo_feeder_url');
        $this->migrator->delete('kampus.neo_feeder_username');
        $this->migrator->delete('kampus.neo_feeder_password');
    }
};