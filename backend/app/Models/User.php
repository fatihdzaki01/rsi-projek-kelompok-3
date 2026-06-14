<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    public $incrementing = true;
    protected $keyType = 'int';

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
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'tanggal_lahir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}