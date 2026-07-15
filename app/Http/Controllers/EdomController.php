<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliahDosen;
use App\Models\KrsDetail;
use App\Models\LpmEdomJawaban;
use App\Models\LpmEdomProgress;
use App\Models\LpmEdomSaran;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EdomController extends Controller
{
    /**
     * Simpan hasil pengisian EDOM mahasiswa untuk SATU dosen pada SATU jadwal_kuliah.
     * Untuk kelas team-teaching, endpoint ini dipanggil sekali per dosen dari sisi
     * frontend (mis. wizard/step form: dosen 1 -> submit -> dosen 2 -> submit).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jadwal_kuliah_id'         => ['required', 'uuid', 'exists:jadwal_kuliah,id'],
            'dosen_id'                 => ['required', 'uuid', 'exists:trx_dosen,id'],
            'jawaban'                  => ['required', 'array', 'min:1'],
            'jawaban.*.pertanyaan_id'  => ['required', 'integer', 'exists:lpm_kuisioner_pertanyaan,id'],
            'jawaban.*.nilai'          => ['required'],
            'saran'                    => ['nullable', 'string', 'max:5000'],
        ]);

        $mahasiswa = $request->user()->mahasiswa; // sesuaikan dgn relasi auth di project Anda
        abort_if(!$mahasiswa, 403, 'Akun ini bukan akun mahasiswa.');

        // ---------------------------------------------------------------
        // OTORISASI SAJA — query di bawah TIDAK PERNAH ditulis ke tabel
        // jawaban/saran. Fungsinya murni memastikan mahasiswa berhak mengisi.
        // ---------------------------------------------------------------
        $terdaftar = KrsDetail::query()
            ->whereHas('krs', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
            ->where('jadwal_kuliah_id', $validated['jadwal_kuliah_id'])
            ->exists();
        abort_unless($terdaftar, 403, 'Anda tidak terdaftar pada kelas ini.');

        $dosenValid = JadwalKuliahDosen::query()
            ->where('jadwal_kuliah_id', $validated['jadwal_kuliah_id'])
            ->where('dosen_id', $validated['dosen_id'])
            ->where('is_penilai', 1)
            ->exists();
        abort_unless($dosenValid, 422, 'Dosen tidak valid untuk dievaluasi pada kelas ini.');

        try {
            DB::transaction(function () use ($validated, $mahasiswa) {
                // 1) TULIS PROGRESS DULU. Unique key baru
                //    (mahasiswa_id, jadwal_kuliah_id, dosen_id) akan melempar
                //    QueryException 1062 kalau mahasiswa submit dua kali atau
                //    ada race-condition (dua request bersamaan) — request kedua
                //    otomatis batal SEBELUM sempat menulis satu pun baris jawaban.
                //    Ini yang membuat alur race-condition proof tanpa perlu
                //    SELECT-then-INSERT / lock manual.
                LpmEdomProgress::create([
                    'mahasiswa_id'     => $mahasiswa->id,
                    'jadwal_kuliah_id' => $validated['jadwal_kuliah_id'],
                    'dosen_id'         => $validated['dosen_id'],
                    'is_completed'     => 1,
                ]);

                // 2) Simpan jawaban SECARA ANONIM.
                //    Tidak ada mahasiswa_id atau krs_detail_id di baris ini.
                $rows = collect($validated['jawaban'])->map(fn ($j) => [
                    'jadwal_kuliah_id' => $validated['jadwal_kuliah_id'],
                    'dosen_id'         => $validated['dosen_id'],
                    'pertanyaan_id'    => $j['pertanyaan_id'],
                    'jawaban_nilai'    => $j['nilai'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ])->all();

                LpmEdomJawaban::insert($rows);

                // 3) Saran kualitatif — juga anonim.
                if (!empty($validated['saran'])) {
                    LpmEdomSaran::create([
                        'jadwal_kuliah_id' => $validated['jadwal_kuliah_id'],
                        'dosen_id'         => $validated['dosen_id'],
                        'catatan'          => $validated['saran'],
                    ]);
                }
            });
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw ValidationException::withMessages([
                    'jadwal_kuliah_id' => 'Anda sudah mengisi EDOM untuk dosen ini pada kelas tersebut.',
                ]);
            }
            throw $e;
        }

        return response()->json(['message' => 'Evaluasi berhasil disimpan.']);
    }
}