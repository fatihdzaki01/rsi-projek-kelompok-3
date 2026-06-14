<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowKomunitas extends Model
{
    protected $table = 'follow_komunitas';
    protected $primaryKey = 'id_follow';

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
}