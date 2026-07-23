<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\DispensasiAkademik;
use App\Models\Kelas;
use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\KeuanganSaldo;
use App\Models\Krs;
use App\Models\MahasiswaBiodata;
use App\Models\MahasiswaKelas;
use App\Models\MasterKurikulum;
use App\Models\PerkuliahanAbsensi;
use App\Models\RefAngkatan;
use App\Models\RefPerson;
use App\Models\RefProdi;
use App\Models\RefProgram;
use App\Models\RiwayatProdiMahasiswa;
use App\Models\RiwayatStatusMahasiswa;
use App\Models\TagihanMahasiswa;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait HasStudentProfileRelations
 *
 * Mengelompokkan seluruh relasi Eloquent & accessor yang dipakai khusus
 * oleh halaman Student Profile (MahasiswaInfolist), supaya Model Mahasiswa
 * utama tidak membengkak dan tetap mudah dipelihara.
 *
 * Cara pakai pada app/Models/Mahasiswa.php:
 *
 *     use App\Models\Concerns\HasStudentProfileRelations;
 *
 *     class Mahasiswa extends Model
 *     {
 *         use HasStudentProfileRelations;
 *         ...
 *     }
 */
trait HasStudentProfileRelations
{
    /*
    |--------------------------------------------------------------------
    | RELASI IDENTITAS
    |--------------------------------------------------------------------
    */

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function biodata(): HasOne
    {
        return $this->hasOne(MahasiswaBiodata::class, 'mahasiswa_id');
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(RefAngkatan::class, 'angkatan_id', 'id_tahun');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(RefProgram::class, 'program_id');
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(MasterKurikulum::class, 'kurikulum_id');
    }

    /*
    |--------------------------------------------------------------------
    | RELASI KELAS & DOSEN PA (PENASEHAT AKADEMIK)
    |--------------------------------------------------------------------
    | mahasiswa_kelas tidak punya kolom "is_active", status aktif
    | ditentukan dari tanggal_keluar yang masih NULL. Kami ambil baris
    | dengan tanggal_masuk paling akhir di antara baris yang masih aktif
    | (latestOfMany) sehingga selalu mendapatkan kelas mahasiswa saat ini.
    */

    public function riwayatKelas(): HasMany
    {
        return $this->hasMany(MahasiswaKelas::class, 'mahasiswa_id')
            ->orderByDesc('tanggal_masuk');
    }

    public function kelasAktif(): HasOne
    {
        return $this->hasOne(MahasiswaKelas::class, 'mahasiswa_id')
            ->whereNull('tanggal_keluar')
            ->latestOfMany('tanggal_masuk');
    }

    /**
     * Dosen Penasehat Akademik (PA) saat ini.
     *
     * Diturunkan dari kelas aktif mahasiswa -> kelas_dosen_wali (is_primary = 1)
     * -> trx_dosen -> ref_person. Bukan relasi langsung sehingga diakses lewat
     * accessor, gunakan withDosenWaliEagerLoad() saat query index/list agar
     * tidak N+1.
     */
    public function getDosenWaliAttribute(): ?RefPerson
    {
        $this->loadMissing('kelasAktif');

        dd([
            'relation_type'   => get_class($this->kelasAktif()),
            'property_type'   => get_debug_type($this->kelasAktif),
            'property_class'  => is_object($this->kelasAktif) ? get_class($this->kelasAktif) : null,
            'property_value'  => $this->kelasAktif,
        ]);
    }
    // public function getDosenWaliAttribute(): ?RefPerson
    // {
    //     /** @var MahasiswaKelas|null $kelas */
    //     $kelas = $this->relationLoaded('kelasAktif')
    //         ? $this->kelasAktif
    //         : $this->kelasAktif()->first();

    //     if (! $kelas) {
    //         return null;
    //     }

    //     /** @var Kelas|null $kelasModel */
    //     $kelasModel = $kelas->relationLoaded('kelas')
    //         ? $kelas->kelas
    //         : $kelas->kelas()->with('dosenWaliUtama.dosen.person')->first();

    //     return $kelasModel?->dosenWaliUtama?->dosen?->person;
    // }

    /**
     * Eager-load chain lengkap untuk resolusi Dosen PA tanpa N+1,
     * dipanggil dari Infolist / Resource query():
     *
     *      Mahasiswa::query()->withDosenWaliEagerLoad()
     */
    public function scopeWithDosenWaliEagerLoad($query)
    {
        return $query->with([
            'kelasAktif.kelas.dosenWaliUtama.dosen.person',
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | RELASI AKADEMIK
    |--------------------------------------------------------------------
    */

    public function riwayatProdi(): HasMany
    {
        return $this->hasMany(RiwayatProdiMahasiswa::class, 'mahasiswa_id')
            ->orderByDesc('tanggal_berlaku');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusMahasiswa::class, 'mahasiswa_id')
            ->with('tahunAkademik')
            ->orderByDesc('tahun_akademik_id');
    }

    /**
     * Baris status akademik (IPK, IPS, SKS, status kuliah) TERAKHIR.
     *
     * Asumsi: tahun_akademik_id bertambah kronologis seiring tahun ajaran
     * dibuat (auto increment), sehingga MAX(tahun_akademik_id) = tahun
     * ajaran terbaru. Jika penomoran tahun akademik pernah tidak berurutan,
     * ganti implementasi ini dengan join eksplisit ke ref_tahun_akademik
     * (order by tanggal_mulai desc).
     */
    public function statusTerakhir(): HasOne
    {
        return $this->hasOne(RiwayatStatusMahasiswa::class, 'mahasiswa_id')
            ->latestOfMany('tahun_akademik_id');
    }

    public function dispensasiAkademik(): HasMany
    {
        return $this->hasMany(DispensasiAkademik::class, 'mahasiswa_id')
            ->orderByDesc('created_at');
    }

    /*
    |--------------------------------------------------------------------
    | RELASI KRS
    |--------------------------------------------------------------------
    */

    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'mahasiswa_id')
            ->orderByDesc('tahun_akademik_id');
    }

    public function krsTerakhir(): HasOne
    {
        return $this->hasOne(Krs::class, 'mahasiswa_id')
            ->latestOfMany('tahun_akademik_id');
    }

    /*
    |--------------------------------------------------------------------
    | RELASI KEUANGAN
    |--------------------------------------------------------------------
    */

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id')
            ->orderByDesc('created_at');
    }

    public function saldo(): HasOne
    {
        return $this->hasOne(KeuanganSaldo::class, 'mahasiswa_id');
    }

    public function beasiswa(): HasMany
    {
        return $this->hasMany(KeuanganMahasiswaBeasiswa::class, 'mahasiswa_id')
            ->with('beasiswa')
            ->orderByDesc('is_active')
            ->orderByDesc('created_at');
    }

    public function beasiswaAktif(): HasMany
    {
        return $this->beasiswa()->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------
    | RELASI NILAI (lintas seluruh KRS, via HasManyThrough)
    |--------------------------------------------------------------------
    | krs_detail tidak punya kolom mahasiswa_id langsung, tapi terhubung
    | 1 tingkat lewat krs. Ini pas untuk HasManyThrough (maks. 1 tabel
    | perantara), sehingga tidak perlu whereHas manual seperti presensi.
    */

    public function nilai(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\KrsDetail::class,
            Krs::class,
            'mahasiswa_id',   // FK di tabel krs -> mahasiswas.id
            'krs_id',         // FK di tabel krs_detail -> krs.id
            'id',             // local key mahasiswas.id
            'id'              // local key krs.id
        )->orderByDesc('krs_detail.created_at');
    }

    /*
    |--------------------------------------------------------------------
    | ACCESSOR RINGKASAN / STATISTIK (dipakai di header & stats grid)
    |--------------------------------------------------------------------
    */

    /**
     * Ringkasan presensi mahasiswa lintas KRS (opsional filter per tahun
     * akademik). Tidak dibuat sebagai relasi Eloquent karena perkuliahan_absensi
     * terhubung ke mahasiswa melalui 2 tingkat pivot (krs -> krs_detail),
     * lebih dari batas hasManyThrough (1 tingkat), sehingga query manual
     * terukur lebih tepat dan efisien lewat whereHas.
     *
     * @return array{total:int,hadir:int,izin:int,sakit:int,alpha:int,persentase:float}
     */
    public function getRingkasanPresensiAttribute(): array
    {
        $base = PerkuliahanAbsensi::query()->whereHas('krsDetail.krs', function ($query): void {
            $query->where('mahasiswa_id', $this->id);
        });

        $total = (clone $base)->count();
        $hadir = (clone $base)->where('status_kehadiran', 'H')->count();
        $izin = (clone $base)->where('status_kehadiran', 'I')->count();
        $sakit = (clone $base)->where('status_kehadiran', 'S')->count();
        $alpha = (clone $base)->where('status_kehadiran', 'A')->count();

        return [
            'total' => $total,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * Semester ke berapa mahasiswa saat ini berjalan. Didekati dari jumlah
     * baris riwayat_status_mahasiswas (1 baris = 1 semester tercatat).
     * Sesuaikan bila institusi mempunyai sumber "semester berjalan" yang
     * lebih otoritatif (mis. kolom khusus di tabel lain).
     */
    public function getSemesterBerjalanAttribute(): int
    {
        return $this->relationLoaded('riwayatStatus')
            ? $this->riwayatStatus->count()
            : $this->riwayatStatus()->count();
    }

    /**
     * Total SKS yang sudah LULUS (nilai_huruf mahasiswa termasuk dalam
     * skala nilai yang is_lulus = 1), dihitung dari seluruh krs_detail
     * yang sudah published.
     */
    public function getTotalSksLulusAttribute(): int
    {
        return (int) \App\Models\KrsDetail::query()
            ->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
            ->join('ref_skala_nilai', 'ref_skala_nilai.huruf', '=', 'krs_detail.nilai_huruf')
            ->where('krs.mahasiswa_id', $this->id)
            ->where('krs_detail.is_published', true)
            ->where('ref_skala_nilai.is_lulus', true)
            ->sum('krs_detail.sks_snapshot');
    }

    /**
     * Total SKS yang pernah diambil (akumulasi seluruh KRS, tanpa melihat
     * status kelulusan), diambil dari total_sks_diambil tiap KRS.
     */
    public function getTotalSksDiambilAttribute(): int
    {
        return (int) ($this->relationLoaded('krs') ? $this->krs : $this->krs()->get())
            ->sum('total_sks_diambil');
    }

    public function getTotalKrsAttribute(): int
    {
        return $this->relationLoaded('krs') ? $this->krs->count() : $this->krs()->count();
    }

    public function getTotalMataKuliahAttribute(): int
    {
        return (int) \App\Models\KrsDetail::query()
            ->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
            ->where('krs.mahasiswa_id', $this->id)
            ->distinct('krs_detail.mata_kuliah_id')
            ->count('krs_detail.mata_kuliah_id');
    }

    /**
     * Total tagihan, total bayar, dan sisa tagihan mahasiswa (akumulasi
     * seluruh tahun akademik, kolom sisa_tagihan sudah GENERATED di DB).
     *
     * @return array{total_tagihan:float,total_bayar:float,sisa_tagihan:float}
     */
    public function getRingkasanKeuanganAttribute(): array
    {
        $tagihan = $this->relationLoaded('tagihan') ? $this->tagihan : $this->tagihan()->get();

        return [
            'total_tagihan' => (float) $tagihan->sum('total_tagihan'),
            'total_bayar' => (float) $tagihan->sum('total_bayar'),
            'sisa_tagihan' => (float) $tagihan->sum('sisa_tagihan'),
        ];
    }

    /**
     * Label human-readable untuk status_kuliah (char(1)) sesuai konvensi
     * PDDIKTI/Forlap yang lazim dipakai SIAKAD: A=Aktif, C=Cuti, D=Drop Out,
     * K=Keluar/Mengundurkan Diri, L=Lulus, N=Non-Aktif/Mangkir.
     * Sesuaikan mapping ini bila institusi memakai kamus kode berbeda.
     */
    public static function labelStatusKuliah(?string $kode): string
    {
        return match ($kode) {
            'A' => 'Aktif',
            'C' => 'Cuti Akademik',
            'D' => 'Drop Out',
            'K' => 'Keluar / Mengundurkan Diri',
            'L' => 'Lulus',
            'N' => 'Non-Aktif',
            default => 'Tidak Diketahui',
        };
    }

    public static function warnaStatusKuliah(?string $kode): string
    {
        return match ($kode) {
            'A' => 'success',
            'C' => 'warning',
            'L' => 'info',
            'D', 'K' => 'danger',
            'N' => 'gray',
            default => 'gray',
        };
    }
}
