<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model dokumen_komunitas (dokumen legalitas: NPWP, Akta, SK, dll).
 * Punya uploaded_at + verified_at (tidak ada updated_at default).
 */
class DokumenKomunitas extends Model
{
    protected $table = 'dokumen_komunitas';
    protected $primaryKey = 'id_dokumen';
    public $timestamps = false;

    protected $fillable = [
        'id_komunitas', 'id_jenis_dok', 'file_url', 'status_verifikasi',
        'alasan_penolakan', 'diverifikasi_oleh', 'uploaded_at', 'verified_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public const STATUS_MENUNGGU     = 'menunggu';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_DITOLAK      = 'ditolak';

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumen::class, 'id_jenis_dok', 'id_jenis_dok');
    }
}
