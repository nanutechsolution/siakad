<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SkalaNilaiSeeder::class,
            RefStatusVerifikasiPembayaranSeeder::class,
            BankKampusSeeder::class,
            FakultasProdiSeeder::class,
            TahunAkademikSeeder::class,
            AturanSksSeeder::class,
            RefJabatanSeeder::class,
            RefGelarSeeder::class,
            SkalaNilaiSeeder::class,
            KomponenBiayaSeeder::class,
            ProgramKelasSeeder::class,
            MasterMatakuliahTISeeder::class,
            KurikulumTISeeder::class,
            MasterMatakuliahPtiSeeder::class,
            MasterMatakuliahTLSeeder::class,
            MasterKurikulumPTISeeder::class,
            MasterKurikulumTLSeeder::class,
            KurikulumMataKuliahTLSeeder::class,
            MasterMatakuliahArsSeeder::class,
            MasterKurikulumArsSeeder::class,
            KurikulumMataKuliahArsSeeder::class,
            MasterMatakuliahBisnisDigitalSeeder::class,
            KurikulumPTISeeder::class,
            MasterMatakuliahK3Seeder::class,
            MasterMatakuliahMISeeder::class,
            KomponenNilaiSeeder::class,
            RefRuangSeeder::class,
            RealDosenSeeder::class,
            RealMahasiswaSeeder::class,
            RefStatusVerifikasiPembayaranSeeder::class,
            MasterBeasiswaSeeder::class,
            PaymentPolicySeeder::class,
            BackupPermissionSeeder::class,

        ]);
    }
}
