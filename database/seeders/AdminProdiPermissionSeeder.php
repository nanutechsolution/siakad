<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminProdiPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Admin Prodi')
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            $this->command->warn('Role Admin Prodi tidak ditemukan.');
            return;
        }

        $permissions = [

            // Jadwal
            'ViewAny:JadwalKuliah',
            'View:JadwalKuliah',

            // Kelas
            'ViewAny:Kelas',
            'View:Kelas',
            'Create:Kelas',
            'Update:Kelas',
            'Delete:Kelas',
            'DeleteAny:Kelas',

            // KRS
            'ViewAny:Krs',
            'View:Krs',

            // Kurikulum MK
            'ViewAny:KurikulumMataKuliah',
            'View:KurikulumMataKuliah',
            'Create:KurikulumMataKuliah',
            'Update:KurikulumMataKuliah',
            'Delete:KurikulumMataKuliah',
            'DeleteAny:KurikulumMataKuliah',
            'Restore:KurikulumMataKuliah',
            'ForceDelete:KurikulumMataKuliah',
            'ForceDeleteAny:KurikulumMataKuliah',
            'RestoreAny:KurikulumMataKuliah',
            'Replicate:KurikulumMataKuliah',
            'Reorder:KurikulumMataKuliah',

            // Mahasiswa
            'ViewAny:Mahasiswa',
            'View:Mahasiswa',
            'Create:Mahasiswa',
            'Update:Mahasiswa',
            'Reorder:Mahasiswa',

            // Master Kurikulum
            'ViewAny:MasterKurikulum',
            'View:MasterKurikulum',
            'Create:MasterKurikulum',
            'Update:MasterKurikulum',

            // Master Mata Kuliah
            'ViewAny:MasterMataKuliah',
            'View:MasterMataKuliah',
            'Create:MasterMataKuliah',
            'Update:MasterMataKuliah',

            // Fakultas
            'ViewAny:RefFakultas',
            'View:RefFakultas',
            'Create:RefFakultas',
            'Update:RefFakultas',

            // Dosen
            'ViewAny:TrxDosen',
            'View:TrxDosen',
            'Create:TrxDosen',
            'Update:TrxDosen',
            'Replicate:TrxDosen',
            'Reorder:TrxDosen',

            // User
            'ViewAny:User',
            'View:User',
            'Create:User',
            'Update:User',
            'Reorder:User',

            // Pages
            'View:DashboardAkademik',
            'View:AkademikKrsPendingList',
            'View:AkademikOverview',
            'View:AkademikProdiChart',

            // Widgets
            'View:LatestActivityWidget',
        ];


        $existingPermissions = Permission::whereIn(
            'name',
            $permissions
        )
        ->where('guard_name', 'web')
        ->get();


        $role->syncPermissions($existingPermissions);


        $this->command->info(
            'Permission Admin Prodi berhasil disinkronkan.'
        );
    }
}