<?php

declare(strict_types=1);

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

return [

    /*
    |--------------------------------------------------------------------------
    | Shield Resource
    |--------------------------------------------------------------------------
    |
    | Here you may configure the built-in role management resource. You can
    | customize the URL, choose whether to show model paths, group it under
    | a cluster, and decide which permission tabs to display.
    |
    */

    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => true,
        'cluster' => null,
        'tabs' => [
            'pages' => true,
            'widgets' => true,
            'resources' => true,
            'custom_permissions' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | When your application supports teams, Shield will automatically detect
    | and configure the tenant model during setup. This enables tenant-scoped
    | roles and permissions throughout your application.
    |
    */

    'tenant_model' => null,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This value contains the class name of your user model. This model will
    | be used for role assignments and must implement the HasRoles trait
    | provided by the Spatie\Permission package.
    |
    */

    'auth_provider_model' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | Here you may define a super admin that has unrestricted access to your
    | application. You can choose to implement this via Laravel's gate system
    | or as a traditional role with all permissions explicitly assigned.
    |
    */

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel User
    |--------------------------------------------------------------------------
    |
    | When enabled, Shield will create a basic panel user role that can be
    | assigned to users who should have access to your Filament panels but
    | don't need any specific permissions beyond basic authentication.
    |
    */

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Builder
    |--------------------------------------------------------------------------
    |
    | You can customize how permission keys are generated to match your
    | preferred naming convention and organizational standards. Shield uses
    | these settings when creating permission names from your resources.
    |
    | Supported formats: snake, kebab, pascal, camel, upper_snake, lower_snake
    |
    */

    'permissions' => [
        'separator' => ':',
        'case' => 'pascal',
        'generate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    |
    | Shield can automatically generate Laravel policies for your resources.
    | When merge is enabled, the methods below will be combined with any
    | resource-specific methods you define in the resources section.
    |
    */

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'deleteAny',
            'restore',
            'forceDelete',
            'forceDeleteAny',
            'restoreAny',
            'replicate',
            'reorder',
        ],
        'single_parameter_methods' => [
            'viewAny',
            'create',
            'deleteAny',
            'forceDeleteAny',
            'restoreAny',
            'reorder',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Shield supports multiple languages out of the box. When enabled, you
    | can provide translated labels for permissions to create a more
    | localized experience for your international users.
    |
    */

    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield.resource_permission_prefixes_labels',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Here you can fine-tune permissions for specific Filament resources.
    | Use the 'manage' array to override the default policy methods for
    | individual resources, giving you granular control over permissions.
    |
    */

    'resources' => [
        'subject' => 'model',
        'manage' => [
            RoleResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
            ],
        ],
        'exclude' => [
            //
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Most Filament pages only require view permissions. Pages listed in the
    | exclude array will be skipped during permission generation and won't
    | appear in your role management interface.
    |
    */

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            Dashboard::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | Like pages, widgets typically only need view permissions. Add widgets
    | to the exclude array if you don't want them to appear in your role
    | management interface.
    |
    */

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Permissions
    |--------------------------------------------------------------------------
    |
    | Sometimes you need permissions that don't map to resources, pages, or
    | widgets. Define any custom permissions here and they'll be available
    | when editing roles in your application.
    |
    */

    'custom_permissions' => [

        /*
    |--------------------------------------------------------------------------
    | PMB
    |--------------------------------------------------------------------------
    */
        'approve_camaba'          => 'Approve Calon Mahasiswa',
        'verifikasi_berkas_pmb'   => 'Verifikasi Berkas PMB',
        'luluskan_camaba'         => 'Luluskan Calon Mahasiswa',
        'sinkron_pmb'            => 'Sinkronisasi PMB',

        /*
    |--------------------------------------------------------------------------
    | Akademik
    |--------------------------------------------------------------------------
    */
        'generate_krs'           => 'Generate KRS',
        'approve_krs'            => 'Approve KRS',
        'tolak_krs'              => 'Tolak KRS',
        'lock_krs'               => 'Lock KRS',
        'unlock_krs'             => 'Unlock KRS',

        'input_nilai'            => 'Input Nilai',
        'revisi_nilai'           => 'Revisi Nilai',
        'publish_nilai'          => 'Publish Nilai',
        'kunci_nilai'            => 'Kunci Nilai',
        'buka_kunci_nilai'       => 'Buka Kunci Nilai',

        'generate_khs'           => 'Generate KHS',
        'generate_transkrip'     => 'Generate Transkrip',

        /*
    |--------------------------------------------------------------------------
    | Keuangan
    |--------------------------------------------------------------------------
    */
        'generate_tagihan'       => 'Generate Tagihan',
        'hapus_tagihan'          => 'Hapus Tagihan',
        'regenerate_tagihan'     => 'Regenerate Tagihan',

        'approve_pembayaran'     => 'Approve Pembayaran',
        'tolak_pembayaran'       => 'Tolak Pembayaran',

        'update_nilai_diskon'    => 'Update Nilai Diskon',
        'beri_dispensasi'        => 'Beri Dispensasi',
        'hapus_dispensasi'       => 'Hapus Dispensasi',

        /*
    |--------------------------------------------------------------------------
    | Mahasiswa
    |--------------------------------------------------------------------------
    */
        'nonaktifkan_mahasiswa'  => 'Nonaktifkan Mahasiswa',
        'aktifkan_mahasiswa'     => 'Aktifkan Mahasiswa',

        'drop_out_mahasiswa'     => 'Drop Out Mahasiswa',
        'cuti_mahasiswa'         => 'Cuti Mahasiswa',
        'aktifkan_kembali'       => 'Aktifkan Kembali Mahasiswa',

        /*
    |--------------------------------------------------------------------------
    | Dosen
    |--------------------------------------------------------------------------
    */
        'ploting_dosen'          => 'Ploting Dosen',
        'ubah_dosen_wali'        => 'Ubah Dosen Wali',

        /*
    |--------------------------------------------------------------------------
    | Kurikulum
    |--------------------------------------------------------------------------
    */
        'publish_kurikulum'      => 'Publish Kurikulum',
        'copy_kurikulum'         => 'Copy Kurikulum',

        /*
    |--------------------------------------------------------------------------
    | Jadwal
    |--------------------------------------------------------------------------
    */
        'generate_jadwal'        => 'Generate Jadwal',
        'publish_jadwal'         => 'Publish Jadwal',

        /*
    |--------------------------------------------------------------------------
    | Wisuda
    |--------------------------------------------------------------------------
    */
        'validasi_yudisium'      => 'Validasi Yudisium',
        'approve_yudisium'       => 'Approve Yudisium',

        'cetak_transkrip'        => 'Cetak Transkrip',
        'cetak_ijazah'           => 'Cetak Ijazah',
        'cetak_skpi'             => 'Cetak SKPI',

        /*
    |--------------------------------------------------------------------------
    | PDDIKTI
    |--------------------------------------------------------------------------
    */
        'sinkron_pddikti'        => 'Sinkronisasi PDDIKTI',
        'retry_sinkron_pddikti'  => 'Retry Sinkronisasi PDDIKTI',

        /*
    |--------------------------------------------------------------------------
    | SDM
    |--------------------------------------------------------------------------
    */
        'approve_mutasi_pegawai' => 'Approve Mutasi Pegawai',
        'approve_jabatan'        => 'Approve Jabatan Pegawai',

        /*
    |--------------------------------------------------------------------------
    | Sistem
    |--------------------------------------------------------------------------
    */
        'backup_database'        => 'Backup Database',
        'restore_database'       => 'Restore Database',

        'manage_setting'         => 'Kelola Pengaturan Sistem',
        'manage_master_data'     => 'Kelola Master Data',

        'lihat_audit_log'        => 'Lihat Audit Log',
        'export_data'            => 'Export Data',
        'import_data'            => 'Import Data',

        /*
    |--------------------------------------------------------------------------
    | Beasiswa
    |--------------------------------------------------------------------------
    */
        'buka_pendaftaran_beasiswa'     => 'Buka Pendaftaran Beasiswa',
        'tutup_pendaftaran_beasiswa'    => 'Tutup Pendaftaran Beasiswa',

        'verifikasi_berkas_beasiswa'    => 'Verifikasi Berkas Beasiswa',
        'approve_beasiswa'              => 'Approve Beasiswa',
        'tolak_beasiswa'                => 'Tolak Beasiswa',

        'tetapkan_penerima_beasiswa'    => 'Tetapkan Penerima Beasiswa',
        'batalkan_penerima_beasiswa'    => 'Batalkan Penerima Beasiswa',

        'aktifkan_beasiswa'             => 'Aktifkan Beasiswa',
        'nonaktifkan_beasiswa'          => 'Nonaktifkan Beasiswa',

        'generate_tagihan_beasiswa'     => 'Generate Potongan Tagihan Beasiswa',
        'sinkron_beasiswa_ke_tagihan'   => 'Sinkronisasi Beasiswa ke Tagihan',

        'cetak_sk_beasiswa'             => 'Cetak SK Beasiswa',
        'export_penerima_beasiswa'      => 'Export Data Penerima Beasiswa',
        'update_manfaat_beasiswa'  => 'Update Manfaat Beasiswa',
        'terapkan_beasiswa'        => 'Terapkan Beasiswa',
        'batalkan_beasiswa'        => 'Batalkan Beasiswa',
        'hentikan_beasiswa'        => 'Hentikan Beasiswa',


        // Adjustment Keuangan
        'CreateKeuanganAdjustment'   => 'Create Keuangan Adjustment',
        'UpdateKeuanganAdjustment'   => 'Update Keuangan Adjustment',
        'DeleteKeuanganAdjustment'   => 'Delete Keuangan Adjustment',

        'SubmitKeuanganAdjustment'   => 'Submit Keuangan Adjustment',
        'ApproveKeuanganAdjustment'  => 'Approve Keuangan Adjustment',
        'RejectKeuanganAdjustment'   => 'Reject Keuangan Adjustment',

        'PostKeuanganAdjustment'     => 'Post Keuangan Adjustment',
        'UnpostKeuanganAdjustment'   => 'Unpost Keuangan Adjustment',

        'CancelKeuanganAdjustment'   => 'Cancel Keuangan Adjustment',
        'PrintKeuanganAdjustment'    => 'Print Keuangan Adjustment',
        'ExportKeuanganAdjustment'   => 'Export Keuangan Adjustment',
        'download-backup' => 'Download Backup',
        'delete-backup'   => 'Delete Backup',
        'create-backup'   => 'Create Backup',
    ],
    /*
    |--------------------------------------------------------------------------
    | Entity Discovery
    |--------------------------------------------------------------------------
    |
    | By default, Shield only looks for entities in your default Filament
    | panel. Enable these options if you're using multiple panels and want
    | Shield to discover entities across all of them.
    |
    */

    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => false,
        'discover_all_pages' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Policy
    |--------------------------------------------------------------------------
    |
    | Shield can automatically register a policy for role management itself.
    | This lets you control who can manage roles using Laravel's built-in
    | authorization system. Requires a RolePolicy class in your app.
    |
    */

    'register_role_policy' => true,

];
