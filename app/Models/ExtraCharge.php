<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraCharge extends Model
{
    protected $table = 'extra_charge';

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