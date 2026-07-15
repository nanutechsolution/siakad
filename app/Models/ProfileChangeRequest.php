<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileChangeRequest extends Model
{
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
        return \App\Models\User::where('person_id', $this->mahasiswa->person_id)->first();
    }

    /**
     * Terapkan perubahan ke ref_person (dipanggil dari panel admin).
     */
    public function approve(User $admin): void
    {
        $person = $this->mahasiswa->person;
        $person->update([$this->field_name => $this->new_value]);

        $this->update([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

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
            body: 'Alasan: ' . ($note ?? '-'),
            success: false,
        );
    }

    protected function notifyPemohon(string $title, string $body, bool $success): void
    {
        $user = $this->pemohonUser();

        if (! $user) {
            return;
        }

        \Filament\Notifications\Notification::make()
            ->title($title)
            ->body($body)
            ->{$success ? 'success' : 'danger'}()
            ->sendToDatabase($user);
    }
}
