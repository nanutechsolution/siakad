<?php

return [
    'label' => 'Log Aktivitas',
    'plural_label' => 'Log Aktivitas',

    'table' => [
        'column' => [
            'id' => 'ID',
            'log_name' => 'Nama Log',
            'event' => 'Aktivitas',
            'risk' => 'Risiko',
            'subject_id' => 'ID Data',
            'subject_type' => 'Jenis Data',
            'causer_id' => 'ID Pengguna',
            'causer_type' => 'Jenis Pengguna',
            'properties' => 'Properti',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diperbarui Pada',
            'description' => 'Deskripsi',
            'subject' => 'Data',
            'causer' => 'Pengguna',
            'ip_address' => 'Alamat IP',
            'browser' => 'Browser',
        ],

        'filter' => [
            'event' => 'Aktivitas',
            'risk' => 'Risiko',
            'created_at' => 'Tanggal Dibuat',
            'created_from' => 'Dari Tanggal',
            'created_until' => 'Sampai Tanggal',
            'causer' => 'Pengguna',
            'subject_type' => 'Jenis Data',
            'batch' => 'Batch UUID',
        ],
    ],


    'infolist' => [
        'section' => [
            'activity_details' => 'Detail Aktivitas',
        ],

        'tab' => [
            'overview' => 'Ringkasan',
            'changes' => 'Perubahan',
            'raw_data' => 'Data Mentah',
            'old' => 'Sebelumnya',
            'new' => 'Sesudah',
        ],

        'entry' => [
            'log_name' => 'Nama Log',
            'event' => 'Aktivitas',
            'created_at' => 'Waktu',
            'description' => 'Deskripsi',
            'subject' => 'Data',
            'causer' => 'Pengguna',
            'ip_address' => 'Alamat IP',
            'browser' => 'Browser',
            'attributes' => 'Atribut',
            'old' => 'Nilai Lama',
            'key' => 'Kolom',
            'value' => 'Nilai',
            'properties' => 'Properti',
        ],
    ],


    'action' => [

        'timeline' => [
            'label' => 'Linimasa',
            'empty_state_title' => 'Belum ada aktivitas',
            'empty_state_description' => 'Belum terdapat aktivitas yang tercatat untuk data ini.',
        ],

        'delete' => [
            'confirmation' => 'Apakah Anda yakin ingin menghapus log aktivitas ini? Tindakan ini tidak dapat dibatalkan.',
            'heading' => 'Hapus Log Aktivitas',
            'button' => 'Hapus',
        ],

        'revert' => [
            'label' => 'Kembalikan',
            'heading' => 'Kembalikan Perubahan',
            'confirmation' => 'Apakah Anda yakin ingin mengembalikan perubahan ini? Nilai lama akan dipulihkan.',
            'button' => 'Kembalikan',
            'success' => 'Perubahan berhasil dikembalikan.',
            'no_old_data' => 'Tidak ada data lama untuk dikembalikan.',
            'nothing_selected' => 'Tidak ada atribut yang dipilih.',
            'subject_not_found' => 'Data tidak ditemukan.',
            'helper_text' => 'Mengubah dari ":old" kembali menjadi ":new"',
        ],

        'restore' => [
            'label' => 'Pulihkan',
            'heading' => 'Pulihkan Data',
            'confirmation' => 'Apakah Anda yakin ingin memulihkan data yang telah dihapus?',
            'success' => 'Data berhasil dipulihkan.',
        ],

        'prune' => [
            'label' => 'Bersihkan Log',
            'heading' => 'Bersihkan Log Aktivitas',
            'confirmation' => 'Apakah Anda yakin ingin menghapus log sebelum tanggal yang dipilih? Tindakan ini tidak dapat dibatalkan.',
            'success' => ':count log aktivitas berhasil dihapus.',
            'date' => 'Hapus log sebelum',
        ],

        'export' => [
            'filename' => 'log_aktivitas',
            'notification' => [
                'completed' => 'Export log aktivitas selesai. :successful_rows :rows_label berhasil diekspor.',
                'failed_rows' => ':count data gagal diekspor.',
            ],
        ],

        'batch' => [
            'label' => 'Batch',
        ],

        'bulk' => [

            'delete' => [
                'confirmation' => 'Apakah Anda yakin ingin menghapus log aktivitas yang dipilih?',
            ],

            'restore' => [
                'label' => 'Pulihkan Terpilih',
                'confirmation' => 'Apakah Anda yakin ingin memulihkan data yang dipilih?',
                'success' => ':count data berhasil dipulihkan.',
            ],

            'revert' => [
                'label' => 'Kembalikan Terpilih',
                'confirmation' => 'Apakah Anda yakin ingin mengembalikan perubahan pada log yang dipilih?',
                'success' => ':count log berhasil dikembalikan.',
            ],
        ],
    ],


    'widgets' => [

        'latest_activity' => 'Aktivitas Terbaru',

        'activity_chart' => [
            'heading' => 'Aktivitas Dari Waktu ke Waktu',
            'label' => 'Aktivitas',
        ],

        'heatmap' => [
            'heading' => 'Peta Aktivitas',
            'less' => 'Sedikit',
            'more' => 'Banyak',
            'tooltip' => ':count aktivitas pada :date',
        ],

        'stats' => [
            'total_activities' => 'Total Aktivitas',
            'total_description' => 'Jumlah seluruh log dalam sistem',
            'top_causer' => 'Pengguna Teraktif',
            'top_causer_description' => ':count aktivitas',
            'top_subject' => 'Data Terbanyak Diubah',
            'top_subject_description' => ':count perubahan',
            'high_risk' => 'Risiko Tinggi',
            'high_risk_description' => 'Aktivitas berisiko tinggi atau kritis',
            'no_data' => 'Tidak ada data',
        ],
    ],


    'pages' => [

        'user_activities' => [
            'title' => 'Aktivitas Pengguna',
            'heading' => 'Aktivitas Pengguna',
            'description_title' => 'Pelacakan Aktivitas Pengguna',
            'description' => 'Melihat seluruh aktivitas yang dilakukan pengguna dalam aplikasi. Filter berdasarkan pengguna, jenis aktivitas, atau data untuk melihat riwayat lengkap.',
        ],

        'audit_dashboard' => [
            'title' => 'Dashboard Audit',
        ],
    ],


    'event' => [
        'created' => 'Dibuat',
        'updated' => 'Diperbarui',
        'deleted' => 'Dihapus',
        'restored' => 'Dipulihkan',
    ],


    'filter' => [
        'causer' => 'Pengguna',
        'event' => 'Jenis Aktivitas',
        'subject_type' => 'Jenis Data',
    ],


    'dashboard' => [
        'title' => 'Dashboard Audit',
    ],


    'filters' => 'Filter',

    'system' => 'Sistem',

    'row' => 'baris',

    'rows' => 'baris',
];
