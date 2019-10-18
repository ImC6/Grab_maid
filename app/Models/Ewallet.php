<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ewallet extends Model
{
    protected $table = 'ewallet';

    // public $timestamps = false;

    protected $fillable = [
        'id',
        'amount',
        'name',
    ];

    protected $hidden = [
        'user_id'
    ];

}