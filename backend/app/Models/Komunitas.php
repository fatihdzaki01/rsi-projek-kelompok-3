<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model komunitas (lembaga pengelola campaign).
 * One-to-one dengan users (role = KOMUNITAS).
 */
class Komunitas extends Model
{
    protected $table = 'komunitas';
    protected $primaryKey = 'id_komunitas';
    public $timestamps = true; // ada created_at & updated_at

    protected $fillable = [
        'id_user', 'id_jenis_lembaga', 'nama_lembaga', 'deskripsi',
        'kode_wilayah', 'rt', 'rw', 'kode_pos', 'alamat_detail',
        'nomor_kontak', 'link_medsos', 'foto_lembaga_url',
        'nama_bank', 'nomor_rekening', 'foto_buku_rekening_url',
        'status', 'alasan_penolakan', 'direview_oleh',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_MENUNGGU       = 'menunggu';
    public const STATUS_AKTIF          = 'aktif';
    public const STATUS_DITOLAK        = 'ditolak';
    public const STATUS_DINONAKTIFKAN  = 'dinonaktifkan';

    // ===== Relationships =====
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function jenisLembaga(): BelongsTo
    {
        return $this->belongsTo(JenisLembaga::class, 'id_jenis_lembaga', 'id_jenis');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenKomunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'id_komunitas', 'id_komunitas');
    }

    public function verifikasiRekening(): HasMany
    {
        return $this->hasMany(VerifikasiRekening::class, 'id_komunitas', 'id_komunitas');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(FollowKomunitas::class, 'id_komunitas', 'id_komunitas');
    }

    // ===== Scopes =====
    public function scopeAktif($q) { return $q->where('status', self::STATUS_AKTIF); }
}
