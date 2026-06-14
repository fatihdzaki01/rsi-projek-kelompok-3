<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Model users (Donatur, Komunitas, SuperAdmin).
 * Catatan: tabel memakai kolom `password_hash`, bukan `password`.
 */
class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = true;
    protected $keyType = 'int';

    // Tabel users punya created_at & updated_at => timestamps aktif
    public $timestamps = true;

    protected $fillable = [
        'username', 'email', 'password_hash', 'role', 'is_active', 'is_verified',
        'foto_profil_url', 'nama_lengkap', 'nomor_telepon', 'jenis_kelamin',
        'tanggal_lahir', 'kode_wilayah',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_verified' => 'boolean',
        'tanggal_lahir' => 'date',
        'deleted_at'  => 'datetime',
    ];

    // Role constants
    public const ROLE_DONATUR    = 'DONATUR';
    public const ROLE_KOMUNITAS  = 'KOMUNITAS';
    public const ROLE_SUPERADMIN = 'SUPERADMIN';

    /**
     * Laravel auth default mencari kolom `password`.
     * Override agar memakai `password_hash` sesuai DDL.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ===== JWT (tymon/jwt-auth) =====
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role];
    }

    // ===== Relationships =====
    public function komunitas(): HasOne
    {
        return $this->hasOne(Komunitas::class, 'id_user', 'id_user');
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class, 'id_user', 'id_user');
    }

    // ===== Helpers =====
    public function isKomunitas(): bool   { return $this->role === self::ROLE_KOMUNITAS; }
    public function isSuperadmin(): bool  { return $this->role === self::ROLE_SUPERADMIN; }
}
