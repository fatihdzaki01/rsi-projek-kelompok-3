<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    protected $table = 'laporan_campaign';
    protected $primaryKey = 'id_laporan';

    protected $fillable = [
        'id_update',
        'id_user',
        'guest_ip',
        'alasan_laporan',
        'status',
    ];
}