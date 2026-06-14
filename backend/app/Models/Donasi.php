<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model donasi. Hanya punya created_at (tidak ada updated_at).
 */
class Donasi extends Model
{
    protected $table = 'donasi';
    protected $primaryKey = 'id_donasi';

    public $timestamps = false; // hanya created_at, tidak ada updated_at

    protected $fillable = [
        'id_user', 'id_campaign', 'nominal', 'metode_pembayaran',
        'nama_tampil', 'is_anonim', 'status_pembayaran', 'bukti_pdf_url',
        'created_at',
    ];

    protected $casts = [
        'nominal'    => 'integer',
        'is_anonim'  => 'boolean',
        'created_at' => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_BERHASIL = 'berhasil';
    public const STATUS_GAGAL    = 'gagal';

    // ===== Relationships =====
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'id_campaign', 'id_campaign');
    }

    public function scopeBerhasil($q) { return $q->where('status_pembayaran', self::STATUS_BERHASIL); }
}
