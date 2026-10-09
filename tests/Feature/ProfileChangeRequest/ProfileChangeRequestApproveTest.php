<?php

declare(strict_types=1);

namespace Tests\Feature\ProfileChangeRequest;

use App\Models\Mahasiswa;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Test approval ProfileChangeRequest (mapping field → Person/Mahasiswa).
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml mengarah ke DB produksi).
 * Semua record dibuat oleh test ini sendiri dan dibersihkan di tearDown().
 */
class ProfileChangeRequestApproveTest extends TestCase
{
    private string $suffix;

    private int $fakultasId = 0;

    private int $prodiId = 0;

    private int $angkatanId;

    private bool $angkatanBaruDibuat = false;

    /** @var int[] */
    private array $personIds = [];

    /** @var string[] */
    private array $mahasiswaIds = [];

    /** @var string[] */
    private array $userIds = [];

    /** @var int[] */
    private array $requestIds = [];

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = 'TST'.strtoupper(Str::random(6));
        $this->angkatanId = (int) now()->format('Y');
        $this->seedFixtures();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();

        parent::tearDown();
    }

    public function test_approve_nisn_writes_to_mahasiswas(): void
    {
        [$mahasiswa, $request] = $this->makeRequest('nisn', null, '9988776655');

        $request->approve($this->admin);

        $mahasiswa->refresh();
        $request->refresh();

        expect($mahasiswa->nisn)->toBe('9988776655')
            ->and($request->status)->toBe('approved')
            ->and($request->reviewed_by)->toBe($this->admin->id)
            ->and($request->reviewed_at)->not->toBeNull()
            ->and($mahasiswa->person->nik)->toBeNull(); // person tidak tersentuh
    }

    public function test_approve_nik_writes_to_person_not_mahasiswa(): void
    {
        [$mahasiswa, $request] = $this->makeRequest('nik', null, '3333333333333333');

        $request->approve($this->admin);

        $mahasiswa->refresh();

        expect($mahasiswa->person->nik)->toBe('3333333333333333')
            ->and($mahasiswa->nisn)->toBeNull()
            ->and($request->refresh()->status)->toBe('approved');
    }

    public function test_approve_nama_lengkap_writes_to_person(): void
    {
        [$mahasiswa, $request] = $this->makeRequest('nama_lengkap', 'Mahasiswa Lama '.$this->suffix, 'Mahasiswa Baru '.$this->suffix);

        $request->approve($this->admin);

        expect($mahasiswa->person->refresh()->nama_lengkap)->toBe('Mahasiswa Baru '.$this->suffix)
            ->and($request->refresh()->status)->toBe('approved');
    }

    public function test_approve_unknown_field_is_rejected_without_changes(): void
    {
        [$mahasiswa, $request] = $this->makeRequest('email_pegawai', 'lama@x.test', 'baru@x.test');

        try {
            $request->approve($this->admin);
            $this->fail('approve() seharusnya melempar exception untuk field tak dikenal.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('tidak dikenal', $e->getMessage());
        }

        $request->refresh();

        expect($request->status)->toBe('pending')
            ->and($request->reviewed_by)->toBeNull()
            ->and($mahasiswa->person->refresh()->email)->toBeNull();
    }

    public function test_approve_non_pending_request_is_rejected(): void
    {
        [$mahasiswa, $request] = $this->makeRequest('nisn', null, '9988776655');
        $request->update(['status' => 'approved']); // sudah diproses sebelumnya

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak berstatus pending');

        try {
            $request->approve($this->admin);
        } finally {
            // data tidak boleh berubah lagi
            expect($mahasiswa->refresh()->nisn)->toBeNull();
        }
    }

    public function test_failed_target_update_rolls_back_request_status(): void
    {
        // Mahasiswa A sudah punya NISN; mahasiswa B mengajukan NISN sama → unique violation.
        [$mahasiswaA] = $this->makeRequest('nisn', null, '1111111111');
        $mahasiswaA->update(['nisn' => '1111111111']);

        [$mahasiswaB, $requestB] = $this->makeRequest('nisn', null, '1111111111');

        try {
            $requestB->approve($this->admin);
            $this->fail('approve() seharusnya gagal karena NISN sudah dipakai mahasiswa lain.');
        } catch (\Throwable) {
            // unique constraint violation → transaction rollback
        }

        $requestB->refresh();

        expect($mahasiswaB->refresh()->nisn)->toBeNull()          // target tidak berubah
            ->and($requestB->status)->toBe('pending')              // status request ikut rollback
            ->and($requestB->reviewed_by)->toBeNull()              // reviewed_by ikut rollback
            ->and($this->notificationCount($mahasiswaB))->toBe(0); // tidak ada notifikasi
    }

    public function test_bulk_approve_uses_same_approve_method(): void
    {
        [$mahasiswaA, $requestA] = $this->makeRequest('nisn', null, '9988776655');
        [, $requestB] = $this->makeRequest('nik', null, '3333333333333333');

        // Meniru loop di ProfileChangeRequestsTable bulk approve.
        $approved = 0;
        foreach ([$requestA, $requestB] as $record) {
            if ($record->status !== 'pending') {
                continue;
            }
            $record->approve($this->admin);
            $approved++;
        }

        expect($approved)->toBe(2)
            ->and($mahasiswaA->refresh()->nisn)->toBe('9988776655')
            ->and($requestA->refresh()->status)->toBe('approved')
            ->and($requestB->refresh()->status)->toBe('approved');
    }

    private function notificationCount(Mahasiswa $mahasiswa): int
    {
        $user = User::where('person_id', $mahasiswa->person_id)->first();

        if (! $user) {
            return 0;
        }

        return DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->count();
    }

    /**
     * @return array{0: Mahasiswa, 1: ProfileChangeRequest}
     */
    private function makeRequest(string $field, ?string $oldValue, string $newValue): array
    {
        $personId = DB::table('ref_person')->insertGetId([
            'nama_lengkap' => 'Mahasiswa '.$this->suffix.' '.count($this->personIds),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->personIds[] = $personId;

        $nim = substr('T'.$this->suffix.count($this->mahasiswaIds), 0, 20);
        $mahasiswa = Mahasiswa::create([
            'person_id' => $personId,
            'nim' => $nim,
            'angkatan_id' => $this->angkatanId,
            'prodi_id' => $this->prodiId,
        ]);
        $this->mahasiswaIds[] = $mahasiswa->id;

        // Akun pemohon (untuk jalur notifikasi).
        $pemohon = User::create([
            'name' => 'Pemohon '.$this->suffix,
            'username' => substr('U'.$this->suffix.count($this->userIds), 0, 50),
            'email' => substr('u'.$this->suffix.count($this->userIds), 0, 50).'@test.local',
            'password' => bcrypt('secret-password'),
            'person_id' => $personId,
        ]);
        $this->userIds[] = $pemohon->id;

        $request = ProfileChangeRequest::create([
            'mahasiswa_id' => $mahasiswa->id,
            'field_name' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'status' => 'pending',
        ]);
        $this->requestIds[] = $request->id;

        return [$mahasiswa, $request];
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

        $this->admin = User::create([
            'name' => 'Admin '.$this->suffix,
            'username' => substr('A'.$this->suffix, 0, 50),
            'email' => 'admin'.$this->suffix.'@test.local',
            'password' => bcrypt('secret-password'),
        ]);
        $this->userIds[] = $this->admin->id;
    }

    private function cleanupFixtures(): void
    {
        if ($this->requestIds !== []) {
            DB::table('profile_change_requests')->whereIn('id', $this->requestIds)->delete();
        }

        if ($this->requestIds !== []) {
            DB::table('profile_change_requests')->whereIn('id', $this->requestIds)->delete();
        }

        if ($this->userIds !== []) {
            DB::table('activity_log')
                ->where(function ($query) {
                    $query->where('subject_type', User::class)
                        ->whereIn('subject_id', $this->userIds);
                })
                ->orWhere(function ($query) {
                    $query->where('causer_type', User::class)
                        ->whereIn('causer_id', $this->userIds);
                })
                ->delete();
            DB::table('notifications')
                ->where('notifiable_type', (new User)->getMorphClass())
                ->whereIn('notifiable_id', $this->userIds)
                ->delete();
            DB::table('users')->whereIn('id', $this->userIds)->delete();
        }

        if ($this->mahasiswaIds !== []) {
            DB::table('activity_log')
                ->where('subject_type', Mahasiswa::class)
                ->whereIn('subject_id', $this->mahasiswaIds)
                ->delete();
            DB::table('mahasiswas')->whereIn('id', $this->mahasiswaIds)->delete();
        }

        if ($this->personIds !== []) {
            DB::table('activity_log')
                ->where('subject_type', RefPerson::class)
                ->whereIn('subject_id', $this->personIds)
                ->delete();
            DB::table('ref_person')->whereIn('id', $this->personIds)->delete();
        }

        if ($this->prodiId) {
            DB::table('ref_prodi')->where('id', $this->prodiId)->delete();
        }

        if ($this->fakultasId) {
            DB::table('ref_fakultas')->where('id', $this->fakultasId)->delete();
        }

        if ($this->angkatanBaruDibuat) {
            DB::table('ref_angkatan')->where('id_tahun', $this->angkatanId)->delete();
        }
    }
}
