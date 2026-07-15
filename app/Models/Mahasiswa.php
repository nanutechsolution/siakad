<?php

namespace App\Models;

use App\Enums\StatusRisikoAkademikEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mahasiswas';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_tambahan' => 'json',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * Get the person data associated with the student.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    /**
     * Get the program study associated with the student.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the batch (angkatan) associated with the student.
     */
    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(RefAngkatan::class, 'angkatan_id', 'id_tahun');
    }

    /**
     * Get the program class (e.g., Reguler, Eksekutif) associated with the student.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(RefProgram::class, 'program_id');
    }

    /**
     * Get the curriculum associated with the student.
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(MasterKurikulum::class, 'kurikulum_id');
    }

    /**
     * Get the study plans (KRS) of the student.
     */
    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'mahasiswa_id');
    }

    /**
     * Get the bills associated with the student.
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Get the status history of the student.
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusMahasiswa::class, 'mahasiswa_id')->orderBy('tahun_akademik_id');
    }
    public function tagihanMahasiswas(): HasMany
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Total tunggakan yang belum lunas (status_bayar != LUNAS).
     * sisa_tagihan sudah generated column di DB (total_tagihan - total_bayar).
     */
    public function totalTunggakan(): float
    {
        return (float) $this->tagihanMahasiswas()
            ->where('status_bayar', '!=', 'LUNAS')
            ->sum('sisa_tagihan');
    }
    /**
     * Relasi ke Kelas (Inverse dari kelas->mahasiswas)
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'mahasiswa_kelas', 'mahasiswa_id', 'kelas_id')
            ->withPivot('id', 'tanggal_masuk', 'tanggal_keluar')
            ->withTimestamps();
    }

    /**
     * Status risiko akademik sederhana berdasar IPK terakhir & tren IPS.
     * Sesuaikan ambang batas (2.00) dengan aturan akademik kampusmu.
     */
    public function statusRisiko(): StatusRisikoAkademikEnum
    {
        $riwayat = $this->riwayatStatus;
        $terakhir = $riwayat->last();

        if (! $terakhir) {
            return StatusRisikoAkademikEnum::BELUM_ADA_DATA;
        }

        if ((float) $terakhir->ipk < 2.00) {
            return  StatusRisikoAkademikEnum::KRITIS;
        }

        $duaTerakhir = $riwayat->slice(-2)->values();
        if ($duaTerakhir->count() === 2 && (float) $duaTerakhir[1]->ips < (float) $duaTerakhir[0]->ips) {
            return StatusRisikoAkademikEnum::WASPADA;
        }

        return StatusRisikoAkademikEnum::AMAN;
    }

    /**
     * Cari akun User (login) milik mahasiswa ini via person_id.
     * Bukan relasi Eloquent karena users<->mahasiswas hanya sibling lewat ref_person,
     * bukan parent-child, jadi query langsung lebih jelas daripada hasOneThrough yang dipaksakan.
     */
    public function akunUser(): ?\App\Models\User
    {
        return \App\Models\User::where('person_id', $this->person_id)->first();
    }

    public function isKrsPaket(): bool
    {
        return ($this->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET';
    }

    public function biodata(): HasOne
    {
        return $this->hasOne(
            MahasiswaBiodata::class,
            'mahasiswa_id',
            'id'
        );
    }
}
