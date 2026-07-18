<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seed 20 role sesuai daftar requirement awal. Jalankan SEBELUM Filament
 * Shield generate permission (php artisan shield:generate), dan sebelum
 * data trx_person_jabatan/trx_dosen/mahasiswas mulai di-seed, karena
 * JabatanRoleSyncService/DosenRoleSyncService/dst akan gagal silent
 * (Spatie melempar RoleDoesNotExist) kalau role belum ada.
 *
 * php artisan db:seed --class=RoleSeeder
 */
final class RoleSeeder extends Seeder
{
    private const GUARD = 'web';

    private const ROLES = [
        'Super Admin',
        'BAAK',
        'Admin Akademik',
        'Admin Fakultas',
        'Admin Prodi',
        'Admin PMB',
        'Admin Keuangan',
        'Kasir',
        'Verifikator Pembayaran',
        'Admin SDM',
        'Admin LPM',
        'Admin LPPM',
        'Pustakawan',
        'Rektor',
        'Wakil Rektor',
        'Dekan',
        'Kaprodi',
        'Dosen',
        'Dosen Wali',
        'Mahasiswa',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => self::GUARD,
            ]);
        }

        $this->command?->info('RoleSeeder: ' . count(self::ROLES) . ' role tersedia.');
    }
}
