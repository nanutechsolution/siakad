<?php

namespace App\Models;

use App\Enums\TahunAkademikStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DbSchema;
use RuntimeException;

class RefTahunAkademik extends Model
{
    protected $table = 'ref_tahun_akademik';

    protected $fillable = [
        'kode_tahun',
        'nama_tahun',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
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
        'feeder_semester_id',
        'config',
    ];

    protected $casts = [
        'status' => TahunAkademikStatus::class,
        'semester' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
        'buka_krs' => 'boolean',
        'is_locked_krs' => 'boolean',
        'buka_input_nilai' => 'boolean',
        'is_locked_nilai' => 'boolean',
        'is_feeder_locked' => 'boolean',
        'last_sync_at' => 'datetime',
        'config' => 'array',
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
        'krs_dibuka_at' => 'datetime',
        'krs_ditutup_at' => 'datetime',
        'nilai_dikunci_at' => 'datetime',
        'nilai_dipublish_at' => 'datetime',
        'semester_ditutup_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->status ??= TahunAkademikStatus::Draft;
            $model->created_by ??= Auth::id();
        });

        static::updating(function (self $model) {
            $model->updated_by = Auth::id();
        });
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------

    public function createdBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    public function activatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'activated_by');
    }

    public function ditutupBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'ditutup_by');
    }

    // ---------------------------------------------------------------
    // Workflow — satu-satunya jalur resmi mengubah status.
    // Setiap method memvalidasi transisi lewat TahunAkademikStatus::canTransitionTo()
    // sehingga tidak mungkin loncat tahap meski dipanggil dari luar Action.
    // ---------------------------------------------------------------

    public function bukaKrs(): void
    {
        DB::transaction(function () {
            // Pastikan hanya satu semester yang aktif dalam satu waktu.
            static::where('id', '!=', $this->id)->where('is_active', true)->update(['is_active' => false]);

            $this->transitionTo(TahunAkademikStatus::KrsBuka, [
                'buka_krs' => true,
                'is_locked_krs' => false,
                'is_active' => true,
                'krs_dibuka_at' => now(),
            ], 'Membuka KRS');
        });
    }

    public function tutupKrs(): void
    {
        $this->transitionTo(TahunAkademikStatus::KrsTutup, [
            'buka_krs' => false,
            'is_locked_krs' => true,
            'krs_ditutup_at' => now(),
        ], 'Menutup KRS');
    }

    public function mulaiPerkuliahan(): void
    {
        $this->transitionTo(TahunAkademikStatus::Perkuliahan, [], 'Memulai periode perkuliahan');
    }

    public function mulaiInputNilai(): void
    {
        $this->transitionTo(TahunAkademikStatus::InputNilai, [
            'buka_input_nilai' => true,
            'tgl_mulai_input_nilai' => $this->tgl_mulai_input_nilai ?? now()->toDateString(),
        ], 'Membuka periode input nilai');
    }

    public function lockNilai(): void
    {
        $this->transitionTo(TahunAkademikStatus::NilaiTerkunci, [
            'buka_input_nilai' => false,
            'is_locked_nilai' => true,
            'nilai_dikunci_at' => now(),
        ], 'Mengunci input nilai');
    }

    public function publishNilai(): void
    {
        DB::transaction(function () {
            $this->transitionTo(TahunAkademikStatus::NilaiPublish, [
                'tgl_publish_nilai' => now()->toDateString(),
                'nilai_dipublish_at' => now(),
            ], 'Mempublish nilai (KHS)');

            // TODO: dispatch(new GenerateKhsJob($this));
        });
    }

    public function tutupSemester(): void
    {
        DB::transaction(function () {
            $this->transitionTo(TahunAkademikStatus::Selesai, [
                'is_active' => false,
                'semester_ditutup_at' => now(),
                'ditutup_by' => Auth::id(),
            ], 'Menutup semester');

            // TODO: dispatch(new HitungIpsIpkJob($this));
            // TODO: dispatch(new GenerateRiwayatAkademikJob($this));
        });
    }

    /**
     * KHUSUS untuk import data historis (angkatan lama yang semesternya sudah lewat).
     * Tidak lewat transitionTo() karena data lama tidak perlu "dijalani ulang" dari Draft —
     * langsung ditandai final. Jangan pernah dipanggil dari Action/UI biasa.
     */
    public static function importHistorical(array $data): self
    {
        $record = new self();
        $record->forceFill(array_merge($data, [
            'status' => TahunAkademikStatus::Selesai,
            'is_active' => false,
        ]));
        $record->save();

        if (function_exists('activity')) {
            activity('tahun_akademik')->performedOn($record)->log('Import data historis');
        }

        return $record;
    }

    protected function transitionTo(TahunAkademikStatus $target, array $extra, string $logMessage): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw new RuntimeException(
                "Transisi status tidak valid: {$this->status->value} -> {$target->value}"
            );
        }

        $this->forceFill($extra);

        $missing = [];
        foreach ($target->requiredFields() as $field => $label) {
            if (blank($this->{$field})) {
                $missing[] = $label;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'Tanggal berikut wajib diisi terlebih dahulu (lewat Edit): ' . implode(', ', $missing)
            );
        }

        $this->status = $target;
        $this->save();

        if (function_exists('activity')) {
            activity('tahun_akademik')
                ->performedOn($this)
                ->causedBy(Auth::user())
                ->log($logMessage);
        }
    }

    // ---------------------------------------------------------------
    // Statistik — dibungkus try/catch supaya widget/tabel tidak error
    // sebelum relasi krs/kelas/nilai disambungkan ke skema Anda.
    // ---------------------------------------------------------------

    public function statistik(): array
    {
        return cache()->remember("tahun_akademik.{$this->id}.stats", now()->addMinutes(5), function () {
            return [
                'mahasiswa_aktif' => $this->safeCount('krs', 'mahasiswa_id'),
                'krs_disetujui' => $this->safeCount('krs', where: ['status' => 'disetujui']),
                'persen_nilai_masuk' => $this->safePercent(),
                'belum_publish' => 0,
            ];
        });
    }
    /**
     * Label status periode input nilai untuk ditampilkan di UI.
     */
    public function inputNilaiStatusLabel(): string
    {
        if ($this->is_locked_nilai) {
            return 'Terkunci (manual)';
        }

        if (! $this->buka_input_nilai) {
            return 'Sudah ditutup';
        }

        $today = now()->startOfDay();

        if ($this->tgl_mulai_input_nilai && $today->lt($this->tgl_mulai_input_nilai)) {
            return 'Belum dibuka';
        }

        if ($this->tgl_selesai_input_nilai && $today->gt($this->tgl_selesai_input_nilai)) {
            return 'Sudah ditutup';
        }

        return 'Terbuka';
    }

    /**
     * Mengecek apakah periode input nilai sedang terbuka.
     *
     * Syarat:
     * - status harus InputNilai
     * - buka_input_nilai harus true
     * - tidak sedang dikunci
     * - tanggal mulai, jika ada, sudah tercapai
     * - tanggal selesai, jika ada, belum terlewati
     */
    public function isInputNilaiOpen(): bool
    {
        if ($this->status !== TahunAkademikStatus::InputNilai) {
            return false;
        }

        if (! $this->buka_input_nilai) {
            return false;
        }

        if ($this->is_locked_nilai) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->tgl_mulai_input_nilai && $today->lt($this->tgl_mulai_input_nilai)) {
            return false;
        }

        if ($this->tgl_selesai_input_nilai && $today->gt($this->tgl_selesai_input_nilai)) {
            return false;
        }

        return true;
    }
    protected function safeCount(string $relation, ?string $distinctColumn = null, array $where = []): int
    {
        if (! method_exists($this, $relation) || ! DbSchema::hasTable((new static)->getTable())) {
            return 0;
        }

        try {
            $query = $this->{$relation}();
            foreach ($where as $col => $val) {
                $query->where($col, $val);
            }

            return $distinctColumn ? $query->distinct($distinctColumn)->count($distinctColumn) : $query->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function safePercent(): int
    {
        return 0; // TODO: hitung dari relasi nilai/kelas riil
    }
}
