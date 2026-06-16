<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model update_campaign (progress update dari komunitas).
 */
class UpdateCampaign extends Model
{
    protected $table = 'update_campaign';
    protected $primaryKey = 'id_update';
    public $timestamps = true;

    protected $fillable = [
        'id_campaign', 'id_komunitas', 'judul_update', 'konten', 'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'id_campaign', 'id_campaign');
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(FotoUpdate::class, 'id_update', 'id_update')->orderBy('urutan');
    }
}
