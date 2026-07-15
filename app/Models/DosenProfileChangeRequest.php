<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenProfileChangeRequest extends Model
{
    protected $table = 'dosen_profile_change_requests';

    protected $fillable = [
        'dosen_id', 'field_name', 'old_value', 'new_value',
        'reason', 'attachment_path', 'status',
        'reviewed_by', 'reviewed_at', 'rejection_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function dosen()
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function pemohonUser()
    {
        return User::where('person_id', $this->dosen->person_id)->first();
    }

    public function approve(User $admin): void
    {
        $person = $this->dosen->person;
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

        \Filament\Notifications\Notification::make()
            ->title($title)
            ->body($body)
            ->{$success ? 'success' : 'danger'}()
            ->sendToDatabase($user);
    }
}