<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Enums;

/**
 * Strategi visibilitas data untuk sebuah model.
 *
 * DataVisibilityResolver memilih salah satu dari strategi ini berdasarkan
 * role yang dimiliki user (lihat config/jabatan_role.php -> strategy_roles),
 * lalu menerapkannya ke query Eloquent.
 */
enum ScopeStrategy: string
{
    /** Tidak ada filter organisasi sama sekali (Super Admin, BAAK, dst). */
    case GLOBAL = 'global';

    /** Dibatasi ke seluruh prodi dalam fakultas yang dapat diakses user. */
    case FAKULTAS = 'fakultas';

    /** Dibatasi ke prodi yang dapat diakses user. */
    case PRODI = 'prodi';

    /** Dibatasi ke data milik mahasiswa itu sendiri (person_id user). */
    case OWNERSHIP_MAHASISWA = 'ownership_mahasiswa';

    /** Dibatasi ke data yang terkait dosen tersebut (mis. kelas yang diampu). */
    case OWNERSHIP_DOSEN = 'ownership_dosen';

    /** Dibatasi ke mahasiswa bimbingan dosen wali tersebut. */
    case DOSEN_WALI = 'dosen_wali';

    /**
     * Tidak difilter berdasarkan organisasi (lintas prodi/fakultas), tapi
     * aksesnya sendiri digerbangi oleh permission modul (mis. Admin Keuangan).
     */
    case MODULE_ONLY = 'module_only';
}
