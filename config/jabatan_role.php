<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Auto-derive Role dari Jabatan Struktural
    |--------------------------------------------------------------------------
    |
    | kode_jabatan (ref_jabatan.kode_jabatan) => nama role Spatie (roles.name).
    | Hanya jabatan yang terdaftar di sini yang otomatis memicu assign/revoke
    | role saat trx_person_jabatan dibuat/diakhiri (lihat JabatanRoleSyncService
    | & TrxPersonJabatanObserver). Jabatan struktural lain yang belum di-map,
    | atau jabatan FUNGSIONAL, tidak memicu apa pun di sini.
    |
    | Role operasional (Admin Prodi, Admin Fakultas, Admin Keuangan, Kasir,
    | Verifikator Pembayaran, Admin PMB, Admin SDM, Admin LPM, Admin LPPM,
    | Pustakawan, super_admin, BAAK, Admin Akademik) SENGAJA tidak dimasukkan
    | di sini — role-role tersebut di-assign manual lewat Filament Shield
    | karena tidak punya representasi structural jabatan 1:1.
    |
    */
    'map' => [
        'KAPRODI' => 'Kaprodi',
        'DEKAN' => 'Dekan',
        'REKTOR' => 'Rektor',
        'WAREK' => 'Wakil Rektor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role -> Scope Strategy
    |--------------------------------------------------------------------------
    |
    | Menentukan strategi query-scope apa yang berlaku untuk sekumpulan role.
    | Ini tabel acuan tunggal yang dipakai oleh DataVisibilityResolver,
    | OrganizationResolver, PermissionResolver, dan JabatanContextResolver.
    | Lihat Bagian 8 dokumen blueprint untuk penjelasan lengkap tiap baris.
    |
    */
    'strategy_roles' => [
        'global' => [
            'super_admin',
            'BAAK',
            'Admin Akademik',
            'Rektor',
            'Wakil Rektor',
        ],
        'fakultas' => [
            'Admin Fakultas',
            'Dekan',
        ],
        'prodi' => [
            'Admin Prodi',
            'Kaprodi',
        ],
        'ownership_dosen' => [
            'Dosen',
        ],
        'dosen_wali' => [
            'Dosen Wali',
        ],
        'ownership_mahasiswa' => [
            'Mahasiswa',
        ],
        'module_only' => [
            'Admin Keuangan',
            'Kasir',
            'Verifikator Pembayaran',
            'Admin PMB',
            'Admin SDM',
            'Admin LPM',
            'Admin LPPM',
            'Pustakawan',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Group -> Role Visibility
    |--------------------------------------------------------------------------
    |
    | Daftar role yang boleh MELIHAT grup menu tertentu di Filament (lebih
    | luas dari "boleh kelola" -> Dosen/Mahasiswa boleh LIHAT menu Akademik
    | walau tidak boleh mengelolanya). Dipakai oleh NavigationResolver.
    | Tambahkan grup baru di sini saat modul baru (Beasiswa, Perpustakaan,
    | Wisuda, Alumni, dst) mulai diimplementasikan.
    |
    */
    'navigation_groups' => [
        'akademik' => [
            'super_admin',
            'BAAK',
            'Admin Akademik',
            'Admin Fakultas',
            'Admin Prodi',
            'Kaprodi',
            'Dosen',
            'Dosen Wali',
            'Mahasiswa',
        ],
        'kurikulum' => [
            'super_admin',
            'BAAK',
            'Admin Akademik',
            'Admin Prodi',
            'Kaprodi',
        ],
        'keuangan' => [
            'super_admin',
            'Admin Keuangan',
            'Kasir',
            'Verifikator Pembayaran',
            'Mahasiswa',
        ],
        'pmb' => [
            'super_admin',
            'Admin PMB',
        ],
        'sdm' => [
            'super_admin',
            'Admin SDM',
        ],
        'lpm' => [
            'super_admin',
            'Admin LPM',
        ],
        'lppm' => [
            'super_admin',
            'Admin LPPM',
        ],
        'perpustakaan' => [
            'super_admin',
            'Pustakawan',
            'Dosen',
            'Mahasiswa',
        ],
        'pengaturan' => [
            'super_admin',
        ],
    ],

];
