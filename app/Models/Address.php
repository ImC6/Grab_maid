<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $table = 'addresses';
    protected $fillable = [
        'user_id',
        'address_line',
        'postcode',
        'location_id',
        'location_details',
        'region',
        'city',
        'state',
        'house_no',
        'house_type',
        'house_size',
        'bedrooms',
        'bathrooms',
        'pet',
    ];
    protected $touches = ['user'];
    protected $hidden = ['user_id'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
