<?php

declare(strict_types=1);

namespace Tests\Feature\Oidc;

use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use App\Models\RefPerson;
use App\Models\RiwayatStatusMahasiswa;
use App\Models\TrxDosen;
use App\Models\User;
use App\Services\Oidc\AccountStatusResolver;
use App\Services\Oidc\OidcClaimsBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Aturan status akun (fail-closed) + klaim identitas dosen (nuptk).
 *
 * Tahun akademik aktif TIDAK dimutasi di DB produksi: resolver dipakai lewat
 * subclass yang activeTahunAkademikId() di-override per kasus.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml menunjuk database produksi).
 */
class OidcAccountStatusTest extends TestCase
{
    private string $suffix;

    private int $fakultasId = 0;

    private int $prodiId = 0;

    private int $angkatanId = 0;

    private bool $angkatanBaruDibuat = false;

    private int $tahunId = 0;

    private string $personId = '';

    private string $mahasiswaId = '';

    private string $dosenId = '';

    private string $userId = '';

    private int $riwayatId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = strtoupper(Str::random(8));
        $this->angkatanId = 900000 + random_int(1, 99999);
    }

    protected function tearDown(): void
    {
        if ($this->riwayatId !== 0) {
            RiwayatStatusMahasiswa::query()->whereKey($this->riwayatId)->delete();
        }
        if ($this->mahasiswaId !== '') {
            DB::table('activity_log')
                ->where('subject_type', Mahasiswa::class)
                ->where('subject_id', $this->mahasiswaId)->delete();
            DB::table('mahasiswas')->where('id', $this->mahasiswaId)->delete();
        }
        if ($this->dosenId !== '') {
            DB::table('activity_log')
                ->where('subject_type', TrxDosen::class)
                ->where('subject_id', $this->dosenId)->delete();
            DB::table('trx_dosen')->where('id', $this->dosenId)->delete();
        }
        if ($this->userId !== '') {
            DB::table('activity_log')
                ->where('subject_type', User::class)
                ->where('subject_id', $this->userId)->delete();
            DB::table('users')->where('id', $this->userId)->delete();
        }
        if ($this->personId !== '') {
            DB::table('activity_log')
                ->where('subject_type', RefPerson::class)
                ->where('subject_id', $this->personId)->delete();
            DB::table('ref_person')->where('id', $this->personId)->delete();
        }
        if ($this->tahunId !== 0) {
            DB::table('ref_tahun_akademik')->where('id', $this->tahunId)->delete();
        }
        if ($this->prodiId !== 0) {
            DB::table('ref_prodi')->where('id', $this->prodiId)->delete();
        }
        if ($this->fakultasId !== 0) {
            DB::table('ref_fakultas')->where('id', $this->fakultasId)->delete();
        }
        if ($this->angkatanBaruDibuat) {
            DB::table('ref_angkatan')->where('id_tahun', $this->angkatanId)->delete();
        }

        parent::tearDown();
    }

    /**
     * Resolver dengan tahun akademik aktif yang di-override per kasus, supaya
     * tes tidak mengubah is_active tahun akademik produksi.
     */
    private function resolver(?int $tahunAktifId): AccountStatusResolver
    {
        return new class($tahunAktifId) extends AccountStatusResolver
        {
            public function __construct(private readonly ?int $tahunId) {}

            protected function activeTahunAkademikId(): ?int
            {
                return $this->tahunId;
            }
        };
    }

    private function seedFixtures(): void
    {
        $this->fakultasId = DB::table('ref_fakultas')->insertGetId([
            'kode_fakultas' => substr('F-'.$this->suffix, 0, 10),
            'nama_fakultas' => 'Fakultas '.$this->suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->prodiId = DB::table('ref_prodi')->insertGetId([
            'fakultas_id' => $this->fakultasId,
            'kode_prodi_internal' => substr('P'.$this->suffix, 0, 10),
            'nama_prodi' => 'Prodi '.$this->suffix,
            'jenjang' => 'S1',
            'last_nim_seq' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! DB::table('ref_angkatan')->where('id_tahun', $this->angkatanId)->exists()) {
            DB::table('ref_angkatan')->insert([
                'id_tahun' => $this->angkatanId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->angkatanBaruDibuat = true;
        }

        $this->tahunId = DB::table('ref_tahun_akademik')->insertGetId([
            'kode_tahun' => substr($this->suffix.'T', 0, 5),
            'nama_tahun' => '2099/2100',
            'semester' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->personId = (string) DB::table('ref_person')->insertGetId([
            'nama_lengkap' => 'Status '.$this->suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->userId = User::query()->create([
            'name' => 'Status '.$this->suffix,
            'username' => 'S'.$this->suffix,
            'email' => strtolower($this->suffix).'@status-test.invalid',
            'password' => Str::random(48),
            'is_active' => true,
            'must_change_password' => false,
            'person_id' => (int) $this->personId,
        ])->getKey();
    }

    private function createMahasiswa(): string
    {
        return Mahasiswa::query()->create([
            'person_id' => (int) $this->personId,
            'nim' => 'M'.$this->suffix,
            'angkatan_id' => $this->angkatanId,
            'prodi_id' => $this->prodiId,
        ])->getKey();
    }

    private function createRiwayat(string $status, ?int $tahunId = null): void
    {
        $this->riwayatId = RiwayatStatusMahasiswa::query()->insertGetId([
            'mahasiswa_id' => $this->mahasiswaId,
            'tahun_akademik_id' => $tahunId ?? $this->tahunId,
            'status_kuliah' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function freshUser(): User
    {
        return User::query()->findOrFail($this->userId);
    }

    // --- B3: status mahasiswa (fail-closed) ----------------------------------

    public function test_mahasiswa_without_riwayat_on_active_year_is_rejected(): void
    {
        $this->seedFixtures();
        $this->mahasiswaId = $this->createMahasiswa();

        $this->assertFalse(
            $this->resolver($this->tahunId)->isActive($this->freshUser()),
            'Mahasiswa tanpa riwayat status pada tahun akademik aktif harus ditolak.',
        );
    }

    public function test_mahasiswa_without_active_academic_year_is_rejected(): void
    {
        $this->seedFixtures();
        $this->mahasiswaId = $this->createMahasiswa();
        $this->createRiwayat(StatusKuliah::AKTIF->value);

        $this->assertFalse(
            $this->resolver(null)->isActive($this->freshUser()),
            'Tanpa tahun akademik aktif, SSO mahasiswa harus ditolak (fail-closed).',
        );
    }

    public function test_mahasiswa_active_in_current_year_is_allowed(): void
    {
        $this->seedFixtures();
        $this->mahasiswaId = $this->createMahasiswa();
        $this->createRiwayat(StatusKuliah::AKTIF->value);

        $this->assertTrue($this->resolver($this->tahunId)->isActive($this->freshUser()));
    }

    public function test_mahasiswa_non_aktif_in_current_year_is_rejected(): void
    {
        $this->seedFixtures();
        $this->mahasiswaId = $this->createMahasiswa();
        $this->createRiwayat(StatusKuliah::CUTI->value);

        $this->assertFalse($this->resolver($this->tahunId)->isActive($this->freshUser()));
    }

    public function test_inactive_user_is_always_rejected(): void
    {
        $this->seedFixtures();
        $this->mahasiswaId = $this->createMahasiswa();
        $this->createRiwayat(StatusKuliah::AKTIF->value);

        User::query()->whereKey($this->userId)->update(['is_active' => false]);

        $this->assertFalse($this->resolver($this->tahunId)->isActive($this->freshUser()));
    }

    // --- B2: klaim identitas -------------------------------------------------

    public function test_dosen_identity_claims_include_nuptk_and_nidn(): void
    {
        $this->seedFixtures();

        $this->dosenId = (string) Str::uuid();
        DB::table('trx_dosen')->insert([
            'id' => $this->dosenId,
            'person_id' => (int) $this->personId,
            'prodi_id' => $this->prodiId,
            'jenis_dosen' => 'TETAP',
            'nidn' => '1234567890',
            'nuptk' => '9988776655443322',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = $this->freshUser();
        $claims = app(OidcClaimsBuilder::class)->forUser($user, ['siakad_identity']);

        $this->assertSame('1234567890', $claims['nidn']);
        $this->assertSame('9988776655443322', $claims['nuptk']);
        $this->assertSame($user->username, $claims['preferred_username']);
        // Tanpa scope email, klaim email tidak boleh bocor.
        $this->assertArrayNotHasKey('email', $claims);
    }

    public function test_dosen_without_nuptk_omits_the_claim(): void
    {
        $this->seedFixtures();

        $this->dosenId = (string) Str::uuid();
        DB::table('trx_dosen')->insert([
            'id' => $this->dosenId,
            'person_id' => (int) $this->personId,
            'prodi_id' => $this->prodiId,
            'jenis_dosen' => 'TETAP',
            'nidn' => '1234567891',
            'nuptk' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $claims = app(OidcClaimsBuilder::class)->forUser($this->freshUser(), ['siakad_identity']);

        $this->assertArrayNotHasKey('nuptk', $claims);
        $this->assertArrayHasKey('nidn', $claims);
    }

    public function test_identity_claims_absent_without_siakad_identity_scope(): void
    {
        $this->seedFixtures();

        $user = $this->freshUser();
        $claims = app(OidcClaimsBuilder::class)->forUser($user, ['openid']);

        $this->assertSame($user->getKey(), $claims['sub']);
        $this->assertArrayNotHasKey('nuptk', $claims);
        $this->assertArrayNotHasKey('preferred_username', $claims);
    }
}
