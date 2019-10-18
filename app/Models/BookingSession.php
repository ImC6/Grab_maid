<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSession extends Model
{
    protected $table = 'booking_session';
    protected $fillable = [
        'user_id',
        'vendor_service_id',
        'booking_date',
    ];
}
