<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $table = 'user_token';
    protected $fillable = ['user_id', 'access_token', 'device_token'];
    protected $touches = ['user'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
