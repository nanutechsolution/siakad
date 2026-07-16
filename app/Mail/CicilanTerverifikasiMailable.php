<?php

namespace App\Mail;

use App\Models\Mahasiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CicilanTerverifikasiMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private Mahasiswa $mahasiswa, private array $unmet)
    {
    }

    public function build(): self
    {
        $summary = collect($this->unmet)->map(function ($u) {
            $nama = $u['nama'] ?? 'Komponen biaya';
            $terbayar = isset($u['terbayar']) ? number_format((float)$u['terbayar'], 0, ',', '.') : '0';
            $target = isset($u['target']) ? number_format((float)$u['target'], 0, ',', '.') : '0';
            return "{$nama}: Rp {$terbayar} / Rp {$target}";
        })->implode("\n");

        $body = "Cicilan Anda telah diverifikasi, tetapi belum memenuhi syarat pembayaran untuk aktivasi NIM.\n\n" .
            "Rincian yang belum terpenuhi:\n" . $summary .
            "\n\nSilakan selesaikan cicilan sesuai instruksi pembayaran agar NIM dapat diaktifkan.";

        return $this->subject('Cicilan Diverifikasi — Lengkapi Pembayaran untuk Aktivasi NIM')
            ->view('emails.cicilan_terverifikasi')
            ->with([
                'mahasiswa' => $this->mahasiswa,
                'body' => $body,
            ]);
    }
}
