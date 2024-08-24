<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guild_id',
        'channel_id',
        'for',
        'data'
    ];
    
    
    
}
