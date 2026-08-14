<?php

namespace App\Providers;

use App\Domain\Authorization\Observers\MahasiswaObserver;
use App\Domain\Authorization\Observers\TrxDosenObserver;
use App\Domain\Authorization\Observers\TrxPersonJabatanObserver;
use App\Observers\PerkuliahanSesiObserver;
use App\Observers\RiwayatStatusMahasiswaObserver;
use App\Observers\TahunAkademikObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DosenJadwalKuliahPolicy;
use App\Policies\DosenNilaiPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;

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
            fn(\App\Models\User $user, \App\Models\KrsDetail $record) =>
            app(DosenNilaiPolicy::class)->inputNilai($user, $record)
        );

        Gate::define(
            'revisiNilaiDosen',
            fn(\App\Models\User $user, \App\Models\KrsDetail $record) =>
            app(DosenNilaiPolicy::class)->revisiNilai($user, $record)
        );

        Gate::define(
            'nilaiKelasDosen',
            fn(\App\Models\User $user, \App\Models\JadwalKuliah $record) =>
            app(DosenJadwalKuliahPolicy::class)->nilaiKelas($user, $record)
        );

        Gate::define(
            'publishNilaiDosen',
            fn(\App\Models\User $user, \App\Models\JadwalKuliah $record) =>
            app(DosenJadwalKuliahPolicy::class)->publishNilai($user, $record)
        );
        \App\Models\TrxPersonJabatan::observe(TrxPersonJabatanObserver::class);
        \App\Models\TrxDosen::observe(TrxDosenObserver::class);
        \App\Models\Mahasiswa::observe(MahasiswaObserver::class);
        \App\Models\PerkuliahanSesi::observe(PerkuliahanSesiObserver::class);
        \App\Models\RefTahunAkademik::observe(TahunAkademikObserver::class);
        \App\Models\RiwayatStatusMahasiswa::observe(RiwayatStatusMahasiswaObserver::class);
        Relation::enforceMorphMap([
            'user'                  => \App\Models\User::class,
            'tagihan_mahasiswa'     => \App\Models\TagihanMahasiswa::class,
            'tagihan_non_reguler'   => \App\Models\TagihanNonReguler::class,
            'pembayaran_mahasiswa'  => \App\Models\PembayaranMahasiswa::class,
            'keuangan_beasiswa_detail' => \App\Models\KeuanganBeasiswaDetail::class,
            'keuangan_mahasiswa_beasiswa' => \App\Models\KeuanganMahasiswaBeasiswa::class,
            'trx_person_jabatan' => \App\Models\TrxPersonJabatan::class,
            'dispensasi_akademik' => \App\Models\DispensasiAkademik::class,
            'keuangan_adjustment' => \App\Models\KeuanganAdjustment::class,
            'keuangan_saldo'      => \App\Models\KeuanganSaldo::class,
            'keuangan_saldo_transaction'   => \App\Models\KeuanganSaldoTransaction::class,
            'krs' => \App\Models\Krs::class,
            'trx_person_gelar' => \App\Models\TrxPersonGelar::class,
            'Krs_detail' => \App\Models\KrsDetail::class,
            'keuangan_master_beasiswa' => \App\Models\KeuanganMasterBeasiswa::class,
            'trx_pegawai' => \App\Models\TrxPegawai::class,
            'payment_policy' => \App\Models\PaymentPolicy::class,
            'pdf_document' => \App\Models\PdfDocument::class,
            'ref_tahun_akademik' => \App\Models\RefTahunAkademik::class,
            'pembimbing_akademik' => \App\Models\PembimbingAkademik::class,
            'konfigurasi_pembimbing_akademik' => \App\Models\KonfigurasiPembimbingAkademik::class,
            'Kelas' => \App\Models\Kelas::class,
            'mahasiswa_kelas' => \App\Models\MahasiswaKelas::class,
            'mahasiswa' => \App\Models\Mahasiswa::class,
            'akademik_ekuivalensi' => \App\Models\AkademikEkuivalensi::class,
            'akademik_grade_revision_log' => \App\Models\AkademikGradeRevisionLog::class,
            'akademik_transkrip' => \App\Models\AkademikTranskrip::class,
            'bank_kampus' => \App\Models\BankKampus::class,
            'dispensasi_akademik_log' => \App\Models\DispensasiAkademikLog::class,
            'dosen_biodata' => \App\Models\DosenBiodata::class,
            'dosen_dokumen' => \App\Models\DosenDokumen::class,
            'dosen_profile_change_request' => \App\Models\DosenProfileChangeRequest::class,
            'dosen_riwayat_pendidikan' => \App\Models\DosenRiwayatPendidikan::class,
            'edom_progress' => \App\Models\EdomProgress::class,
            'generator_batch' => \App\Models\GeneratorBatch::class,
            'generator_log' => \App\Models\GeneratorLog::class,
            'jadwal_komponen_nilai' => \App\Models\JadwalKomponenNilai::class,
            'jadwal_Kuliah_dosen' => \App\Models\JadwalKuliahDosen::class,
            'jadwal_kuliah' => \App\Models\JadwalKuliah::class,
            'jadwal_ujian' => \App\Models\JadwalUjian::class,
            'keuangan_detail_tarif' => \App\Models\KeuanganDetailTarif::class,
            'keuangan_general_ledger' => \App\Models\KeuanganGeneralLedger::class,
            'keuangan_komponen_biaya' => \App\Models\KeuanganKomponenBiaya::class,
            'keuangan_skema_tarif' => \App\Models\KeuanganSkemaTarif::class,
            'krs_detail_nilai' => \App\Models\KrsDetailNilai::class,
            'krs_status_log' => \App\Models\KrsStatusLog::class,
            'kurikulum_komponen_nilai' => \App\Models\KurikulumKomponenNilai::class,
            'kurikulum_mataKuliah' => \App\Models\KurikulumMataKuliah::class,
            'kurikulum_mk_prasyarat' => \App\Models\KurikulumMkPrasyarat::class,
            'lpm_akreditasi' => \App\Models\LpmAkreditasi::class,
            'lpm_akreditasi_elemen' => \App\Models\LpmAkreditasiElemen::class,
            'perkuliahan_sesi' => \App\Models\PerkuliahanSesi::class,
            'ref_person' => \App\Models\RefPerson::class,
            'trx_dosen' => \App\Models\TrxDosen::class,
            'payment_policy_detail' => \App\Models\PaymentPolicyDetail::class,
            'pdf_signature' => \App\Models\PdfSignature::class,
            'pdf_verification' => \App\Models\PdfVerification::class,
            'perkuliahan_absensi' => \App\Models\PerkuliahanAbsensi::class,
            'tagihan_nonReguler' => \App\Models\TagihanNonReguler::class,
            'tagihan_nonReguler_detail' => \App\Models\TagihanNonRegulerDetail::class,
            'tagihan_mahasiswa_detail' => \App\Models\TagihanMahasiswaDetail::class,
            'ref_gelar' => \App\Models\RefGelar::class,
        ]);
    }
}
