<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefTahunAkademik extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'ref_tahun_akademik';
    public $timestamps = false;
    /**
     * Kolom-kolom yang dapat diisi (sesuai persis dengan skema .sql).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_tahun',
        'nama_tahun',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'buka_krs',
        'is_locked_krs',
        'buka_input_nilai',
        'is_locked_nilai',
        'feeder_semester_id',
        'last_sync_at',
        'is_feeder_locked',
        'config',
        'created_by',
        'updated_by',
        'activated_by',
        'activated_at',
        'tgl_mulai_krs',
        'tgl_selesai_krs',
        'tgl_mulai_perkuliahan',
        'tgl_selesai_perkuliahan',
        'tgl_mulai_uts',
        'tgl_selesai_uts',
        'tgl_mulai_uas',
        'tgl_selesai_uas',
        'tgl_mulai_input_nilai',
        'tgl_selesai_input_nilai',
        'tgl_publish_nilai',
    ];

    /**
     * Konversi tipe data kolom secara otomatis (sesuai tipe data di .sql).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'semester' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
        'buka_krs' => 'boolean',
        'is_locked_krs' => 'boolean',
        'buka_input_nilai' => 'boolean',
        'is_locked_nilai' => 'boolean',
        'last_sync_at' => 'datetime',
        'is_feeder_locked' => 'boolean',
        'config' => 'array', // JSON type in DB
        'activated_at' => 'datetime',
        'tgl_mulai_krs' => 'date',
        'tgl_selesai_krs' => 'date',
        'tgl_mulai_perkuliahan' => 'date',
        'tgl_selesai_perkuliahan' => 'date',
        'tgl_mulai_uts' => 'date',
        'tgl_selesai_uts' => 'date',
        'tgl_mulai_uas' => 'date',
        'tgl_selesai_uas' => 'date',
        'tgl_mulai_input_nilai' => 'date',
        'tgl_selesai_input_nilai' => 'date',
        'tgl_publish_nilai' => 'date',
    ];

    /**
     * Relasi ke tabel users (Berdasarkan FOREIGN KEY di schema).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function jadwalKuliah(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JadwalKuliah::class, 'tahun_akademik_id');
    }

    public function isInputNilaiOpen(): bool
    {
        if ($this->is_locked_nilai) {
            return false;
        }

        if ($this->buka_input_nilai) {
            return true;
        }

        if (!$this->tgl_mulai_input_nilai || !$this->tgl_selesai_input_nilai) {
            return false;
        }

        $today = Carbon::today();
        return $today->betweenIncluded($this->tgl_mulai_input_nilai, $this->tgl_selesai_input_nilai);
    }

    public function inputNilaiStatusLabel(): string
    {
        if ($this->is_locked_nilai) return 'Terkunci oleh Admin';
        if ($this->isInputNilaiOpen()) return 'Terbuka';
        return 'Sudah Ditutup';
    }
}
