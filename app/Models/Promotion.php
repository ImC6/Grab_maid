<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = ['id','promo_code','discount_type', 'description', 'percentage', 'total', 'status', 'start_date', 'end_date'];

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'promotion_id');
    }

    public function isUsed()
    {
        return $this->getAttribute('status') === 0;
    }

    public function isAvailable()
    {
        return $this->getAttribute('status') === 1;
    }

    public function isExp()
    {
        $startDate = $this->getAttribute('start_date');
        $endDate = $this->getAttribute('end_date');
        $today = Carbon::now();

        return false; // todo return between date
    }

    public function scopeCheckCode($query, string $code)
    {
        return $query->where('promo_code', $code)->where('status', 1);
    }
}
