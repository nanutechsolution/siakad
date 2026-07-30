<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,

    /**
     * User (kolom `id` di tabel users) yang dipakai sebagai verified_by
     * saat pembayaran di-auto-verifikasi oleh webhook Midtrans — supaya
     * di audit trail jelas ini diverifikasi sistem, bukan admin manusia.
     * WAJIB diisi sebelum fitur ini dipakai — lihat MIDTRANS-INTEGRATION.md
     * bagian "Seed User Sistem".
     */
    'system_verifier_user_id' => env('MIDTRANS_SYSTEM_VERIFIER_USER_ID'),
];
