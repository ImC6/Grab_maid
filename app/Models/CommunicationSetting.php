<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationSetting extends Model
{
    protected $table = 'communication_setting';
    protected $fillable = [
        'user_id',
        'setting'
    ];
    protected $hidden = [
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
