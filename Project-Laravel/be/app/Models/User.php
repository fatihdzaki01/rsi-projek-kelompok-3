<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model users (Donatur, Komunitas, SuperAdmin).
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'username', 'email', 'password_hash', 'role', 'is_active', 'is_verified',
        'foto_profil_url', 'nama_lengkap', 'nomor_telepon', 'jenis_kelamin',
        'tanggal_lahir', 'kode_wilayah',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'tanggal_lahir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const ROLE_DONATUR = 'DONATUR';
    public const ROLE_KOMUNITAS = 'KOMUNITAS';
    public const ROLE_SUPERADMIN = 'SUPERADMIN';

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function komunitas(): HasOne
    {
        return $this->hasOne(Komunitas::class, 'id_user', 'id_user');
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class, 'id_user', 'id_user');
    }

    public function isKomunitas(): bool { return $this->role === self::ROLE_KOMUNITAS; }
    public function isSuperadmin(): bool { return $this->role === self::ROLE_SUPERADMIN; }
}
