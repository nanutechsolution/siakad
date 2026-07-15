<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenDokumen extends Model
{
    protected $table = 'dosen_dokumen';

    protected $fillable = [
        'dosen_id',
        'ref_dokumen_dosen_id',
        'file_path',
        'nama_file_asli',
        'ukuran_kb',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function dosen()
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(RefDokumenDosen::class, 'ref_dokumen_dosen_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function approve(User $admin): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_note' => null,
        ]);

        $this->notifyPemohon(
            title: 'Dokumen disetujui',
            body: "Dokumen \"{$this->jenisDokumen->nama_dokumen}\" telah diverifikasi dan disetujui.",
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
            title: 'Dokumen ditolak, mohon upload ulang',
            body: 'Alasan: ' . ($note ?? '-'),
            success: false,
        );
    }

    protected function notifyPemohon(string $title, string $body, bool $success): void
    {
        $user = User::where('person_id', $this->dosen->person_id)->first();

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
