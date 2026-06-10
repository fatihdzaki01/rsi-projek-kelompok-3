<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    
    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'is_active',
        'is_verified',
        'foto_profil_url',
        'nama_lengkap',
        'nomor_telepon',
        'jenis_kelamin',
        'tanggal_lahir',
        'kode_wilayah',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_verified'    => 'boolean',
        'tanggal_lahir'  => 'date',
    ];

    // Wajib: Sanctum pakai ini untuk verifikasi password
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}