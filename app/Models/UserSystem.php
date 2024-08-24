<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'guild_id',
        'min_point',
    ];


    /**
     * A relationship to user guild.
     *
     */
    public function guild()
    {
        return $this->belongsTo(UserGuild::class);
    }
}
