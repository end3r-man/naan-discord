<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserYoutube extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'guild_id',
        'youtube_id',
        'name',
        'profile',
        'last'
    ];

    public function channel()
    {
        return $this->belongsTo(UserChannel::class);    
    }
}
