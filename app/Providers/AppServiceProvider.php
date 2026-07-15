<?php

namespace App\Providers;

use App\Models\PembayaranMahasiswa;
use App\Models\PerkuliahanSesi;
use App\Observers\PembayaranMahasiswaObserver;
use App\Observers\PerkuliahanSesiObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DosenJadwalKuliahPolicy;
use App\Policies\DosenNilaiPolicy;

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
        PembayaranMahasiswa::observe(PembayaranMahasiswaObserver::class);
        \App\Models\RefTahunAkademik::observe(\App\Observers\TahunAkademikObserver::class);
    }
}
