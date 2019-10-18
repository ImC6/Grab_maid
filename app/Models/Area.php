<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $fillable = [
        'state_id',
        'city_id',
        'name'
    ];

    public function city()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }

    public function state()
    {
        return $this->belongsTo(\App\Models\State::class, 'state_id');
    }
}
