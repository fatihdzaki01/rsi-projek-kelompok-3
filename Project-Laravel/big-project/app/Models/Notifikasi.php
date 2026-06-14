<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model notifikasi. Penerima XOR (user ATAU komunitas). Wajib expires_at (TTL).
 */
class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notif';
    public $timestamps = false; // ada created_at saja (+ expires_at manual)

    protected $fillable = [
        'id_penerima_user', 'id_penerima_komunitas', 'id_pengirim_user',
        'judul', 'pesan', 'tipe',
        'related_campaign_id', 'related_donasi_id', 'related_update_id',
        'related_verifikasi_id', 'related_pencairan_id',
        'is_read', 'read_at', 'created_at', 'expires_at', 'is_archived', 'archived_at',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'is_archived' => 'boolean',
        'created_at'  => 'datetime',
        'expires_at'  => 'datetime',
        'read_at'     => 'datetime',
        'archived_at' => 'datetime',
    ];

    /** TTL (hari) per tipe — mirror dari fungsi DB get_notification_ttl(). */
    public const TTL_DAYS = [
        'follow' => 30, 'donasi' => 60, 'pencairan' => 90, 'withdrawal' => 90,
        'update_campaign' => 45, 'verifikasi' => 90, 'sistem' => 180,
    ];

    public static function ttlDaysFor(string $tipe): int
    {
        return self::TTL_DAYS[$tipe] ?? 90;
    }
}
