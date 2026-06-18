<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model verifikasi_rekening (request perubahan rekening komunitas).
 * Hanya punya created_at + tanggal_keputusan (tidak ada updated_at).
 */
class VerifikasiRekening extends Model
{
    protected $table = 'verifikasi_rekening';
    protected $primaryKey = 'id_verif';
    public $timestamps = false;

    protected $fillable = [
        'id_komunitas', 'nama_bank_baru', 'nomor_rekening_baru',
        'foto_buku_rekening_url', 'alasan_perubahan', 'status',
        'alasan_penolakan', 'direview_oleh', 'created_at', 'tanggal_keputusan',
    ];

    protected $casts = [
        'created_at'        => 'datetime',
        'tanggal_keputusan' => 'datetime',
    ];

    public const STATUS_MENUNGGU  = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK   = 'ditolak';

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function scopeMenunggu($q) { return $q->where('status', self::STATUS_MENUNGGU); }
}
