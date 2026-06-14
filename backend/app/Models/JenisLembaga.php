<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisLembaga extends Model
{
    protected $table = 'jenis_lembaga';
    protected $primaryKey = 'id_jenis';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_jenis', 'nama_jenis'];
}
