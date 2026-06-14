<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    protected $table = 'jenis_dokumen';
    protected $primaryKey = 'id_jenis_dok';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_jenis_dok', 'nama_dokumen', 'deskripsi', 'wajib_untuk_jenis_lembaga', 'is_opsional'];
    protected $casts = ['is_opsional' => 'boolean'];
}
