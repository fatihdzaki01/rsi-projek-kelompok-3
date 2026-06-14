<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowKomunitas extends Model
{
    protected $table = 'follow_komunitas';
    protected $primaryKey = 'id_follow';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_komunitas',
        'is_active',
        'followed_at',
        'unfollowed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'followed_at' => 'datetime',
        'unfollowed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function komunitas(): BelongsTo
    {
        return $this->belongsTo(Komunitas::class, 'id_komunitas', 'id_komunitas');
    }

    public function scopeAktif($q) { return $q->where('is_active', true); }
}
