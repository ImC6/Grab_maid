<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'region';

    // public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    protected $hidden = [
        'user_id'
    ];

}