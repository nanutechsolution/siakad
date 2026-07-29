<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Settings\KampusSettings;

class KampusSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(KampusSettings::class);

        $settings->nama = 'Universitas Stella Maris Sumba';
        $settings->nama_singkat = 'UNMARIS';
        $settings->alamat = 'Jl. Kampus Universitas Stella Maris Sumba';
        $settings->telepon = '0387xxxxxxx';
        $settings->email = 'info@unmarissumba.ac.id';
        $settings->website = 'https://unmarissumba.ac.id';

        $settings->akreditasi = 'Baik Sekali';
        $settings->logo_path = null;

        // Akademik
        $settings->reset_nim_tahunan = true;
        $settings->semester_aktif = '2026/2027 Ganjil';
        $settings->batas_maksimal_sks = 24;

        // Neo Feeder
        $settings->neo_feeder_url = 'https://feeder.example.com';
        $settings->neo_feeder_username = '';
        $settings->neo_feeder_password = '';

        // Sistem
        $settings->maintenance_mode = false;
        $settings->enable_sso_login = false;

        // SMTP
        $settings->smtp_host = 'smtp.gmail.com';

        $settings->save();
    }
}
