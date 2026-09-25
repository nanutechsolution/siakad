<?php

declare(strict_types=1);

namespace App\Support\Pdf;

use App\Models\JadwalKuliah;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Otorisasi context PDF untuk rute `pdf.download`.
 *
 * Route menerima context dalam bentuk base64(JSON) yang DIPERSETUJUI oleh
 * client, sehingga tanpa pemeriksaan ini mahasiswa mana pun bisa menukar
 * `mahasiswa_id` orang lain dan mengunduh KHS/jadwal/kartu ujian mereka
 * (IDOR murni). Setiap context wajib diperiksa terhadap pemanggil sebelum
 * resolver dijalankan.
 */
final class PdfContextGuard
{
    /**
     * @param  array<string, mixed>  $context
     *
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     */
    public function authorize(Request $request, string $type, array $context): void
    {
        $user = $request->user();

        if ($user === null) {
            throw new AccessDeniedHttpException('Anda harus login untuk mengunduh dokumen ini.');
        }

        // Context yang menyasar satu mahasiswa (KHS, jadwal kuliah, kartu ujian).
        if (array_key_exists('mahasiswa_id', $context)) {
            $mahasiswa = Mahasiswa::find($context['mahasiswa_id']);

            if ($mahasiswa === null) {
                throw new NotFoundHttpException('Mahasiswa tidak ditemukan.');
            }

            // Pemilik data sendiri selalu boleh, selain itu lewat policy
            // (permission + scope organisasi, gagal-tertutup bila tak ada policy).
            $isOwner = $mahasiswa->person_id !== null
                && $mahasiswa->person_id === $user->person_id;

            if (! $isOwner && ! Gate::forUser($user)->allows('view', $mahasiswa)) {
                throw new AccessDeniedHttpException('Anda tidak memiliki akses ke dokumen milik mahasiswa tersebut.');
            }

            return;
        }

        // Context agregat tanpa subyek mahasiswa (mis. Rekap Jadwal Kuliah)
        // = laporan lintas data; wajib permission ViewAny terkait.
        if ($type === 'rekap-jadwal-kuliah') {
            Gate::forUser($user)->authorize('viewAny', JadwalKuliah::class);

            return;
        }

        // Jenis dokumen baru yang belum dikenali jangan dibuka lewat alur ini.
        throw new AccessDeniedHttpException('Jenis dokumen ini tidak dapat diunduh lewat tautan tersebut.');
    }
}
