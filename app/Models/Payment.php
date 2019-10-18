<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'ref_no',
        'type',
        'payment_type',
        'topup_id',
        'amount',
        'desc',
        'status',
    ];

    protected $hidden = [
        'id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function topup()
    {
        return $this->belongsTo(\App\Models\Topup::class, 'topup_id');
    }

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIsBooking($query)
    {
        return $query->type(1);
    }

    public function scopeIsTopup($query)
    {
        return $query->type(2);
    }

    // PAYMENT VALIDATION
    // 'credit_card_no' => 'required|digits:16',
    // 'cvv' => 'required|digits:3',
    // 'cc_exp_date' => 'required|date_format:"m/d"',
}
