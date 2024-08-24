<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guild_id',
        'role_id',
        'point'
    ];
}
