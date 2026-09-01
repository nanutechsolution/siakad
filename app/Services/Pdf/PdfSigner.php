<?php

namespace App\Services\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfDocument;
use App\Models\PdfSignature;
use App\Models\PdfSignatureAuthority;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PdfSigner
{
    /**
     * Resolve daftar penandatangan (READ-ONLY) — dipanggil SEBELUM render,
     * supaya nama & jabatan bisa tampil di dalam dokumen.
     */
    public function resolveSigners(PdfDocumentType $type, array $scope = []): array
    {
        $authorities = PdfSignatureAuthority::query()
            ->where('document_type', $type->value)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        if ($authorities->isEmpty()) {
            throw new RuntimeException("Belum ada konfigurasi penandatangan (pdf_signature_authorities) untuk jenis dokumen [{$type->value}].");
        }

        return $authorities->map(function (PdfSignatureAuthority $authority) use ($scope) {
            $query = DB::table('trx_person_jabatan')
                ->join('ref_person', 'ref_person.id', '=', 'trx_person_jabatan.person_id')
                ->where('trx_person_jabatan.jabatan_id', $authority->jabatan_id)
                ->where('trx_person_jabatan.tanggal_mulai', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('trx_person_jabatan.tanggal_selesai')
                        ->orWhere('trx_person_jabatan.tanggal_selesai', '>=', now());
                });

            $scopeLabel = '';

            if ($authority->scope === 'PRODI') {
                if (empty($scope['prodi_id'])) {
                    throw new RuntimeException(
                        "Otoritas [{$authority->label}] dikonfigurasi untuk tingkat Program Studi, namun dokumen ini tidak memiliki data Program Studi."
                    );
                }
                $query->where('trx_person_jabatan.prodi_id', $scope['prodi_id']);

                // Cek nama prodi ke database berdasarkan prodi_id
                $namaProdi = \Illuminate\Support\Facades\DB::table('ref_prodi')
                    ->where('id', $scope['prodi_id'])
                    ->value('nama_prodi') ?? ('ID ' . $scope['prodi_id']);

                $scopeLabel = " pada Program Studi {$namaProdi}";
            } elseif ($authority->scope === 'FAKULTAS') {
                if (empty($scope['fakultas_id'])) {
                    throw new RuntimeException(
                        "Otoritas [{$authority->label}] dikonfigurasi untuk tingkat Fakultas, namun dokumen ini tidak memiliki data Fakultas."
                    );
                }
                $query->where('trx_person_jabatan.fakultas_id', $scope['fakultas_id']);

                // Cek nama fakultas ke database berdasarkan fakultas_id
                $namaFakultas = \Illuminate\Support\Facades\DB::table('ref_fakultas')
                    ->where('id', $scope['fakultas_id'])
                    ->value('nama_fakultas') ?? ('ID ' . $scope['fakultas_id']);

                $scopeLabel = " pada Fakultas {$namaFakultas}";
            }
            $person = $query
                ->orderByDesc('trx_person_jabatan.tanggal_mulai')
                ->select(['ref_person.id as person_id'])
                ->first();

            $pejabat = null;

            if ($person) {
                $pejabat = \App\Models\RefPerson::with('gelars')
                    ->find($person->person_id);
            }

            if (! $pejabat) {
                throw new RuntimeException(
                    "Tidak ditemukan pejabat aktif untuk jabatan {$authority->label}{$scopeLabel}. " .
                        "Dokumen tidak dapat diterbitkan tanpa penandatangan yang sah."
                );
            }

            return [
                'authorityId' => $authority->id,
                'urutan' => $authority->urutan,
                'label' => $authority->label,
                'personId' => $pejabat->id,
                'namaLengkap' => $pejabat->nama_dengan_gelar,
            ];
        })->all();
    }
    /**
     * Simpan riwayat tanda tangan SETELAH dokumen berhasil dirender & disimpan.
     */
    public function persistSignatures(PdfDocument $document, array $resolvedSigners): void
    {
        foreach ($resolvedSigners as $signer) {
            PdfSignature::create([
                'pdf_document_id' => $document->id,
                'signature_authority_id' => $signer['authorityId'],
                'person_id' => $signer['personId'],
                'nama_penandatangan_snapshot' => $signer['namaLengkap'],
                'jabatan_snapshot' => $signer['label'],
                'urutan' => $signer['urutan'],
                'document_hash_at_signing' => $document->file_hash,
                'signed_at' => now(),
                'status' => 'signed',
            ]);
        }
    }
}
