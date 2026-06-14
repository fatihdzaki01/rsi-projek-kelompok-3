<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model pencairan_dana. Timestamp memakai tanggal_pengajuan & tanggal_keputusan.
 */
class PencairanDana extends Model
{
    protected $table = 'pencairan_dana';
    protected $primaryKey = 'id_pencairan';
    public $timestamps = false;

    protected $fillable = [
        'id_campaign', 'id_komunitas', 'id_laporan_dana', 'urutan_ke',
        'nominal_diajukan', 'nominal_disetujui', 'keterangan', 'url_proposal',
        'nama_bank_tujuan', 'nomor_rekening_tujuan', 'bukti_transfer_url',
        'status', 'alasan_penolakan', 'direview_oleh',
        'tanggal_pengajuan', 'tanggal_keputusan',
    ];

    protected $casts = [
        'nominal_diajukan'  => 'integer',
        'nominal_disetujui' => 'integer',
        'tanggal_pengajuan' => 'datetime',
        'tanggal_keputusan' => 'datetime',
    ];

    public const STATUS_MENUNGGU_REVIEW = 'menunggu_review';
    public const STATUS_DISETUJUI       = 'disetujui';
    public const STATUS_DITOLAK         = 'ditolak';
    public const STATUS_SELESAI         = 'selesai';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'id_campaign', 'id_campaign');
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function scopePending($q) { return $q->where('status', self::STATUS_MENUNGGU_REVIEW); }
}
