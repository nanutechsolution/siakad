<?php

namespace App\Providers;

use App\Domain\Authorization\Observers\KelasDosenWaliObserver;
use App\Domain\Authorization\Observers\MahasiswaObserver;
use App\Domain\Authorization\Observers\TrxDosenObserver;
use App\Domain\Authorization\Observers\TrxPersonJabatanObserver;
use App\Models\KelasDosenWali;
use App\Models\KeuanganBeasiswaDetail;
use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\KeuanganSaldo;
use App\Models\KeuanganSaldoTransaction;
use App\Models\Mahasiswa;
use App\Models\PembayaranMahasiswa;
use App\Models\PerkuliahanSesi;
use App\Models\RefTahunAkademik;
use App\Models\RiwayatStatusMahasiswa;
use App\Models\TagihanMahasiswa;
use App\Models\TagihanNonReguler;
use App\Models\TrxDosen;
use App\Models\TrxPersonJabatan;
use App\Models\User;
use App\Observers\PerkuliahanSesiObserver;
use App\Observers\RiwayatStatusMahasiswaObserver;
use App\Observers\TahunAkademikObserver;
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
        $this->app->singleton(\App\Services\Mahasiswa\NilaiAkademikService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Registrasi Policy berbasis Model (Sangat disarankan & Clean)
        Gate::policy(\App\Models\KrsDetail::class, DosenNilaiPolicy::class);
        Gate::policy(\App\Models\JadwalKuliah::class, DosenJadwalKuliahPolicy::class);
        // 2. Jika tetap ingin mempertahankan Alias String (inputNilaiDosen & revisiNilaiDosen)
        // Bungkus dengan Closure agar parameter $user dan $record diteruskan dengan sempurna
        Gate::define(
            'inputNilaiDosen',
            fn(User $user, \App\Models\KrsDetail $record) =>
            app(DosenNilaiPolicy::class)->inputNilai($user, $record)
        );

        Gate::define(
            'revisiNilaiDosen',
            fn(User $user, \App\Models\KrsDetail $record) =>
            app(DosenNilaiPolicy::class)->revisiNilai($user, $record)
        );

        Gate::define(
            'nilaiKelasDosen',
            fn(User $user, \App\Models\JadwalKuliah $record) =>
            app(DosenJadwalKuliahPolicy::class)->nilaiKelas($user, $record)
        );

        Gate::define(
            'publishNilaiDosen',
            fn(User $user, \App\Models\JadwalKuliah $record) =>
            app(DosenJadwalKuliahPolicy::class)->publishNilai($user, $record)
        );
        TrxPersonJabatan::observe(TrxPersonJabatanObserver::class);
        TrxDosen::observe(TrxDosenObserver::class);
        KelasDosenWali::observe(KelasDosenWaliObserver::class);
        Mahasiswa::observe(MahasiswaObserver::class);
        PerkuliahanSesi::observe(PerkuliahanSesiObserver::class);
        RefTahunAkademik::observe(TahunAkademikObserver::class);
        RiwayatStatusMahasiswa::observe(RiwayatStatusMahasiswaObserver::class);
        Relation::enforceMorphMap([
            'user'                  => User::class,
            'tagihan_mahasiswa'     => TagihanMahasiswa::class,
            'tagihan_non_reguler'   => TagihanNonReguler::class,
            'pembayaran_mahasiswa'  => PembayaranMahasiswa::class,
            'keuangan_beasiswa_detail' => KeuanganBeasiswaDetail::class,
            'keuangan_mahasiswa_beasiswa' => KeuanganMahasiswaBeasiswa::class,
            'trx_person_jabatan' => TrxPersonJabatan::class,
            'dispensasi_akademik' => \App\Models\DispensasiAkademik::class,
            'keuangan_adjustment' => \App\Models\KeuanganAdjustment::class,
            'keuangan_saldo'      => KeuanganSaldo::class,
            'keuangan_saldo_transaction'   => KeuanganSaldoTransaction::class,
            'krs' => \App\Models\Krs::class,
            'trx_person_gelar' => \App\Models\TrxPersonGelar::class,
            'Krs_detail' => \App\Models\KrsDetail::class,
            'keuangan_master_beasiswa' => \App\Models\KeuanganMasterBeasiswa::class,
            'trx_pegawai' => \App\Models\TrxPegawai::class,
            'payment_policy' => \App\Models\PaymentPolicy::class,
        ]);
    }
}
