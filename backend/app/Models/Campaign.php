<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model campaign fundraising.
 */
class Campaign extends Model
{
    protected $table = 'campaign';
    protected $primaryKey = 'id_campaign';
    public $timestamps = true;

    protected $fillable = [
        'id_komunitas', 'id_kategori', 'kode_wilayah', 'judul', 'deskripsi',
        'foto_campaign_url', 'target_dana', 'dana_terkumpul', 'saldo_tersedia',
        'saldo_terkunci', 'tipe_distribusi', 'target_audiens', 'total_penerima_manfaat',
        'jumlah_pencairan_approve', 'potongan_platform_sudah_dipotong',
        'tanggal_mulai', 'tanggal_selesai', 'status', 'alasan_penolakan',
        'url_rab', 'direview_oleh',
    ];

    protected $casts = [
        'target_dana'    => 'integer',
        'dana_terkumpul' => 'integer',
        'saldo_tersedia' => 'integer',
        'saldo_terkunci' => 'integer',
        'tanggal_mulai'  => 'date',
        'tanggal_selesai'=> 'date',
        'potongan_platform_sudah_dipotong' => 'boolean',
        'deleted_at'     => 'datetime',
    ];

    // Status constants (sesuai chk_status_valid)
    public const STATUS_MENUNGGU_REVIEW = 'menunggu_review';
    public const STATUS_AKTIF           = 'aktif';
    public const STATUS_SELESAI         = 'selesai';
    public const STATUS_DITOLAK         = 'ditolak';
    public const STATUS_NONAKTIF        = 'nonaktif';
    public const STATUS_DITUTUP_PERMANEN= 'ditutup_permanen';

    public const TARGET_DANA_MINIMAL = 10000000;

    // ===== Relationships =====
    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriCampaign::class, 'id_kategori', 'id_kategori');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode');
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class, 'id_campaign', 'id_campaign');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(UpdateCampaign::class, 'id_campaign', 'id_campaign');
    }

    public function pencairan(): HasMany
    {
        return $this->hasMany(PencairanDana::class, 'id_campaign', 'id_campaign');
    }

    // ===== Scopes =====
    public function scopeAktif($q)   { return $q->where('status', self::STATUS_AKTIF); }
    public function scopeSelesai($q) { return $q->where('status', self::STATUS_SELESAI); }
}
