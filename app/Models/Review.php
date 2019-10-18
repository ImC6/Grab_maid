<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = ['user_guid', 'booking_id', 'comment', 'rating'];

    protected $hidden = ['id'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_guid', 'guid');
    }

    public function boooking()
    {
        return $this->belongsTo(\App\Models\Booking::class, 'booking_id');
    }
}
