<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriCampaign extends Model
{
    protected $table = 'kategori_campaign';
    protected $primaryKey = 'id_kategori';
    public $incrementing = false; // id_kategori INT (bukan serial)
    public $timestamps = false;

    protected $fillable = ['id_kategori', 'nama_kategori', 'deskripsi', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
