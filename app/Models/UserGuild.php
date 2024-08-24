<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGuild extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'guild_id',
        'guild_name',
    ];
}
