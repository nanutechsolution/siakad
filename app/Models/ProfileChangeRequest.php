<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfileChangeRequest extends Model
{
    /**
     * Whitelist eksplisit: field_name => target penyimpanan.
     *
     * - 'person'    → kolom pada relasi mahasiswa->person (ref_person)
     * - 'mahasiswa' → kolom pada mahasiswas (mis. nisn)
     *
     * Field yang tidak ada di sini DITOLAK saat approve(), sehingga field_name
     * dari database tidak bisa dipakai untuk memodifikasi kolom sembarangan.
     */
    private const FIELD_TARGETS = [
        'nama_lengkap' => 'person',
        'nik' => 'person',
        'tanggal_lahir' => 'person',
        'tempat_lahir' => 'person',
        'jenis_kelamin' => 'person',
        'nisn' => 'mahasiswa',
    ];

    protected $table = 'profile_change_requests';

    protected $fillable = [
        'mahasiswa_id',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * User (akun login) pemilik pengajuan ini, dipakai untuk kirim notifikasi
     * hasil verifikasi ke mahasiswa yang bersangkutan.
     */
    public function pemohonUser()
    {
        return User::where('person_id', $this->mahasiswa->person_id)->first();
    }

    /**
     * Terapkan perubahan sesuai whitelist FIELD_TARGETS (dipanggil dari panel
     * admin, baik approve individual maupun bulk — single source of truth).
     *
     * Atomic: update target + status request di satu transaction; jika salah
     * satu gagal, semuanya rollback. Notifikasi dikirim SETELAH transaction
     * berhasil, sehingga tidak pernah terkirim saat rollback.
     *
     * @throws \RuntimeException jika field tidak dikenal, relasi tidak ada,
     *                           atau request bukan berstatus pending.
     */
    public function approve(User $admin): void
    {
        $target = self::FIELD_TARGETS[$this->field_name] ?? null;

        if ($target === null) {
            throw new \RuntimeException(
                "Field \"{$this->field_name}\" tidak dikenal dan tidak diizinkan untuk di-approve."
            );
        }

        $mahasiswa = $this->mahasiswa;

        if (! $mahasiswa) {
            throw new \RuntimeException(
                "Relasi mahasiswa untuk pengajuan #{$this->id} tidak ditemukan."
            );
        }

        if ($target === 'person' && ! $mahasiswa->person) {
            throw new \RuntimeException(
                "Relasi person untuk mahasiswa {$mahasiswa->nim} tidak ditemukan."
            );
        }

        DB::transaction(function () use ($admin, $target, $mahasiswa) {
            // Kunci baris & cek ulang status di dalam transaction agar approve
            // ganda (mis. klik bersamaan / bulk race) tidak mungkin terjadi.
            $locked = static::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->status !== 'pending') {
                throw new \RuntimeException(
                    "Pengajuan #{$this->id} tidak berstatus pending dan tidak dapat di-approve."
                );
            }

            $field = $locked->field_name;

            if ($target === 'person') {
                $mahasiswa->person->update([$field => $locked->new_value]);
            } else {
                // target 'mahasiswa' — saat ini hanya nisn.
                $mahasiswa->update([$field => $locked->new_value]);
            }

            $locked->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Sinkronkan atribut in-memory instance asli ($this) dengan hasil
            // locking, supaya pemanggil melihat status terbaru.
            $this->setRawAttributes($locked->getAttributes(), true);
        });

        // Hanya terkirim jika transaction di atas sukses (commit).
        $this->notifyPemohon(
            title: 'Pengajuan perubahan data disetujui',
            body: "Perubahan data \"{$this->field_name}\" telah disetujui dan diterapkan.",
            success: true,
        );
    }

    public function reject(User $admin, ?string $note = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_note' => $note,
        ]);

        $this->notifyPemohon(
            title: 'Pengajuan perubahan data ditolak',
            body: 'Alasan: '.($note ?? '-'),
            success: false,
        );
    }

    protected function notifyPemohon(string $title, string $body, bool $success): void
    {
        $user = $this->pemohonUser();

        if (! $user) {
            return;
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->{$success ? 'success' : 'danger'}()
            ->sendToDatabase($user);
    }
}
