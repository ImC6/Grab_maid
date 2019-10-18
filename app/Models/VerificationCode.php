<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    protected $table = 'verification_codes';
    protected $fillable = ['user_id', 'code'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
