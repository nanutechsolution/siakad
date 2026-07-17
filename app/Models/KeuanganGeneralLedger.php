<?php

namespace App\Models;

use App\Enums\TipeTransaksiLedger;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class KeuanganGeneralLedger extends Model
{
    use HasUuids;

    protected $table = 'keuangan_general_ledgers';

    /**
     * Tabel ini cuma punya kolom created_at (tidak ada updated_at),
     * sesuai desain buku besar yang append-only.
     */
    public $timestamps = false;

    protected $fillable = [
        'mahasiswa_id',
        'referensi_dokumen',
        'tipe_transaksi',
        'debit',
        'kredit',
        'saldo_berjalan',
        'keterangan',
    ];

    protected $casts = [
        'tipe_transaksi' => TipeTransaksiLedger::class,
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
        'saldo_berjalan' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        // Ledger WAJIB append-only: baris lama tidak boleh diubah atau
        // dihapus. Koreksi harus berupa entri ADJUSTMENT baru lewat
        // LedgerService, bukan mengedit riwayat.
        static::updating(function () {
            throw new RuntimeException('keuangan_general_ledgers bersifat append-only — tidak boleh di-update.');
        });

        static::deleting(function () {
            throw new RuntimeException('keuangan_general_ledgers bersifat append-only — tidak boleh dihapus.');
        });
    }
}
