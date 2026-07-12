<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefPerson extends Model
{
    use SoftDeletes;

    /**
     * Nama tabel di database sesuai skema asli.
     */
    protected $table = 'ref_person';

    /**
     * Kolom-kolom yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'nik',
        'email',
        'no_hp',
        'tanggal_lahir',
        'jenis_kelamin',
        'tempat_lahir',
        'photo_path',
    ];

    /**
     * Casting tipe data agar sesuai dengan skema SQL.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Relasi ke entitas Dosen.
     * Seorang person bisa terdaftar sebagai Dosen.
     */
    public function dosen(): HasOne
    {
        return $this->hasOne(TrxDosen::class, 'person_id');
    }

    public function trxDosen()
    {
        return $this->hasOne(TrxDosen::class, 'person_id');
    }
    public function jabatans(): HasMany
    {
        return $this->hasMany(TrxPersonJabatan::class, 'person_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(TrxPersonRole::class, 'person_id');
    }

    public function mahasiswa(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'person_id');
    }
    public function getNamaDenganGelarAttribute(): string
    {
        $gelarDepan = $this->gelars
            ->where('posisi', 'DEPAN')
            ->pluck('kode')
            ->implode(' ');

        $gelarBelakang = $this->gelars
            ->where('posisi', 'BELAKANG')
            ->pluck('kode')
            ->implode(', ');

        $nama = $this->nama_lengkap;

        if ($gelarDepan) {
            $nama = "{$gelarDepan} {$nama}";
        }

        if ($gelarBelakang) {
            $nama .= ", {$gelarBelakang}";
        }

        return $nama;
    }

    public function gelars(): BelongsToMany
    {
        return $this->belongsToMany(
            RefGelar::class,
            'trx_person_gelar',
            'person_id',
            'gelar_id'
        )->withPivot('urutan')
            ->orderBy('trx_person_gelar.urutan');
    }
}
