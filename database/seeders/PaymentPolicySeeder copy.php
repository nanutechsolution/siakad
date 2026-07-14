<?php

namespace Database\Seeders;

use App\Domains\Core\Models\ProgramKelas;
use App\Domains\Core\Models\TahunAkademik;
use App\Models\KeuanganKomponenBiaya;
use App\Models\PaymentPolicy;
use App\Models\PaymentPolicyDetail;
use App\Models\RefProgram;
use App\Models\RefTahunAkademik;
use Illuminate\Database\Seeder;

class PaymentPolicySeeder extends Seeder
{
    public function run(): void
    {
        $spp = KeuanganKomponenBiaya::where('nama_komponen', 'Pembayaran SPP')->first();

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
