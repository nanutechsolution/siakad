<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KeuanganKomponenBiaya as KomponenBiaya;
use App\Models\PaymentPolicy;
use App\Models\PaymentPolicyDetail;
use App\Models\RefProgram;
use App\Models\RefTahunAkademik;

class PaymentPolicySeeder extends Seeder
{
    public function run(): void
    {
        $spp = KomponenBiaya::where('nama_komponen', 'Pembayaran SPP')->first();

        if (!$spp) {
            throw new \Exception('Komponen SPP tidak ditemukan');
        }
        $tahunAktif = RefTahunAkademik::where('is_active', true)->first();

        if (!$tahunAktif) {
            throw new \Exception('Tahun akademik aktif belum ada');
        }

        foreach (RefProgram::all() as $kelas) {

            $policy = PaymentPolicy::create([
                'nama' => 'KRS ' . $kelas->nama,
                'tahun_akademik_id' => $tahunAktif->id,
                'program_kelas_id' => $kelas->id,
                'aktif' => true,
            ]);


            PaymentPolicyDetail::create([
                'payment_policy_id' => $policy->id,
                'komponen_biaya_id' => $spp->id,
                'minimal_persen' => 50,
                'wajib' => true,
            ]);
        }
    }
}
