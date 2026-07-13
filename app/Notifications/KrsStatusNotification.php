<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class KrsStatusNotification extends Notification
{
    public function __construct(
        public string $status,       
        public ?string $catatan = null,
        public ?string $tahunAkademik = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->status === 'DISETUJUI' ? 'KRS Disetujui' : 'KRS Ditolak',
            'body' => $this->status === 'DISETUJUI'
                ? 'KRS Anda untuk periode '.($this->tahunAkademik ?? '-').' telah disetujui oleh Dosen Wali.'
                : 'KRS Anda untuk periode '.($this->tahunAkademik ?? '-').' ditolak. Catatan: '.($this->catatan ?: '-'),
            'status' => $this->status,
        ];
    }
}