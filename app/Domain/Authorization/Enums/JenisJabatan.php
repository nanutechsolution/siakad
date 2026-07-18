<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Enums;

/**
 * Mirror dari kolom `ref_jabatan.jenis` (ENUM('STRUKTURAL','FUNGSIONAL')).
 * Hanya jabatan STRUKTURAL yang dipetakan ke Role Spatie secara otomatis
 * oleh JabatanRoleSyncService (lihat config/jabatan_role.php -> map).
 */
enum JenisJabatan: string
{
    case STRUKTURAL = 'STRUKTURAL';
    case FUNGSIONAL = 'FUNGSIONAL';
}
