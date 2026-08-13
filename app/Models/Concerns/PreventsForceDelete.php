<?php

namespace App\Models\Concerns;

use RuntimeException;

/**
 * Tempelkan trait ini (setelah `use SoftDeletes;`) pada model akademik/
 * keuangan kritis untuk memblokir forceDelete() dari jalur normal
 * (Filament, Service, Controller, Tinker web).
 *
 * forceDelete() HANYA bisa berjalan jika:
 *   1. Proses berjalan di console (Artisan/Tinker CLI), DAN
 *   2. Config `siakad.allow_force_delete` di-set true SECARA MANUAL
 *      sebelum command dijalankan (misalnya lewat env var sementara),
 *      bukan default aplikasi.
 *
 * Ini SENGAJA dibuat merepotkan — sesuai rekomendasi audit, force delete
 * pada data akademik/keuangan seharusnya memang tidak pernah menjadi
 * operasi satu-klik.
 *
 * Model yang WAJIB memakai trait ini:
 *   Mahasiswa, TrxDosen, Krs, KrsDetail, Kelas, MasterKurikulum,
 *   RefTahunAkademik, MasterMataKuliah, TagihanMahasiswa,
 *   TagihanNonReguler, GeneratorBatch, SinkronisasiBatch,
 *   MidtransTransaction, MidtransGatewayLog.
 *
 * Contoh pemakaian di model:
 *
 *   use Illuminate\Database\Eloquent\SoftDeletes;
 *   use App\Models\Concerns\PreventsForceDelete;
 *
 *   class Mahasiswa extends Model
 *   {
 *       use SoftDeletes, PreventsForceDelete;
 *   }
 *
 * Cara sah menjalankan force delete (hanya untuk kasus darurat/testing):
 *
 *   SIAKAD_ALLOW_FORCE_DELETE=true php artisan tinker
 *   >>> config(['siakad.allow_force_delete' => true]);
 *   >>> Mahasiswa::withTrashed()->find($id)->forceDelete();
 *
 * Catatan: trait ini memblokir lewat model event `forceDeleting`, yang
 * terpicu untuk forceDelete() per-instance. Untuk query builder mass
 * delete (`Model::withTrashed()->forceDelete()` pada Builder, bukan
 * instance), event Eloquent TIDAK terpicu — karena itu, akses langsung ke
 * DB (raw query / migration DROP) tetap harus dibatasi lewat kontrol akses
 * server/DB, bukan hanya trait ini.
 */
trait PreventsForceDelete
{
    protected static function bootPreventsForceDelete(): void
    {
        static::forceDeleting(function ($model) {
            $allowed = app()->runningInConsole()
                && config('siakad.allow_force_delete', false) === true;

            if (! $allowed) {
                throw new RuntimeException(sprintf(
                    'Force delete pada [%s] (ID: %s) diblokir. Model ini berisi data akademik/keuangan ' .
                        'kritis. Jika benar-benar diperlukan (kasus darurat/testing), jalankan lewat console ' .
                        'dengan config("siakad.allow_force_delete", true) diset eksplisit terlebih dahulu.',
                    static::class,
                    $model->getKey()
                ));
            }
        });
    }
}
