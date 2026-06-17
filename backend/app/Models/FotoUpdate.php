<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model foto_update. Hanya punya uploaded_at.
 */
class FotoUpdate extends Model
{
    protected $table = 'foto_update';
    protected $primaryKey = 'id_foto';
    public $timestamps = false;

    protected $fillable = ['id_update', 'foto_url', 'caption', 'urutan', 'uploaded_at'];

    protected $casts = [
        'urutan'      => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function updatePost(): BelongsTo
    {
        return $this->belongsTo(UpdateCampaign::class, 'id_update', 'id_update');
    }
}
