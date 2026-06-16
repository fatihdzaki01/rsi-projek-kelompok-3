<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notif';

    public $timestamps = false;

    protected $fillable = [
        'id_penerima_user',
        'id_penerima_komunitas',
        'id_pengirim_user',
        'judul',
        'pesan',
        'tipe',
        'related_campaign_id',
        'related_donasi_id',
        'related_update_id',
        'related_verifikasi_id',
        'related_pencairan_id',
        'is_read',
        'read_at',
        'created_at',
        'expires_at',
        'is_archived',
        'archived_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_archived' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * TTL (days) per notification type — after this, the notification expires.
     */
    public static function ttlDaysFor(string $tipe): int
    {
        return match ($tipe) {
            'campaign', 'update_campaign', 'campaign_disetujui', 'campaign_ditolak',
            'campaign_menunggu', 'campaign_hampir_selesai', 'peringatan',
            'verifikasi', 'komunitas_baru', 'user_baru' => 30,

            'donasi_berhasil', 'donasi_gagal', 'donasi_pending',
            'donasi_masuk', 'follow_baru' => 7,

            'welcome' => 90,

            default => 30,
        };
    }

    /**
     * Create a notification in a single call.
     */
    public static function kirim(array $data): self
    {
        return self::create(array_merge([
            'is_read' => false,
            'created_at' => now(),
            'expires_at' => now()->addDays(self::ttlDaysFor($data['tipe'] ?? 'sistem')),
        ], $data));
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function penerimaUser()
    {
        return $this->belongsTo(User::class, 'id_penerima_user', 'id_user');
    }

    public function penerimaKomunitas()
    {
        return $this->belongsTo(Komunitas::class, 'id_penerima_komunitas', 'id_komunitas');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'id_pengirim_user', 'id_user');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'related_campaign_id', 'id_campaign');
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('id_penerima_user', $userId);
    }

    public function scopeForKomunitas($query, int $komunitasId)
    {
        return $query->where('id_penerima_komunitas', $komunitasId);
    }
}
