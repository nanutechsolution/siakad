<?php

namespace App\Providers;

use App\Events\PembayaranTerverifikasi;
use App\Listeners\Pembayaran\GenerateNimListener;
use App\Listeners\Pembayaran\KirimNotifikasiPembayaranListener;
use App\Models\KeuanganBeasiswaDetail;
use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\PembayaranMahasiswa;
use App\Models\PerkuliahanSesi;
use App\Models\TagihanMahasiswa;
use App\Models\TagihanNonReguler;
use App\Models\TrxPersonJabatan;
use App\Models\User;
use App\Observers\PerkuliahanSesiObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DosenJadwalKuliahPolicy;
use App\Policies\DosenNilaiPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define(
            'nilaiKelasDosen',
            [DosenJadwalKuliahPolicy::class, 'nilaiKelas']
        );

        Gate::define(
            'publishNilaiDosen',
            [DosenJadwalKuliahPolicy::class, 'publishNilai']
        );


        Gate::define(
            'inputNilaiDosen',
            [DosenNilaiPolicy::class, 'inputNilai']
        );


        Gate::define(
            'revisiNilaiDosen',
            [DosenNilaiPolicy::class, 'revisiNilai']
        );
        PerkuliahanSesi::observe(PerkuliahanSesiObserver::class);
        \App\Models\RefTahunAkademik::observe(\App\Observers\TahunAkademikObserver::class);
        Relation::enforceMorphMap([
            'user'                  => User::class,
            'tagihan_mahasiswa'     => TagihanMahasiswa::class,
            'tagihan_non_reguler'   => TagihanNonReguler::class,
            'pembayaran_mahasiswa'  => PembayaranMahasiswa::class,
            'keuangan_beasiswa_detail' => KeuanganBeasiswaDetail::class,
            'keuangan_mahasiswa_beasiswa' => KeuanganMahasiswaBeasiswa::class,
            'trx_person_jabatan' => TrxPersonJabatan::class,
        ]);
    }
}
