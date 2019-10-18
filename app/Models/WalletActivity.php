<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletActivity extends Model
{
    protected $table = 'wallet_activities';

    protected $fillable = [
        'wallet_id',
        'amount',
        'action',
        'desc',
    ];

    protected $hidden = [
        'id',
        'wallet_id'
    ];

    public function wallet()
    {
        return $this->belongsTo(\App\Models\Wallet::class, 'wallet_id');
    }
}
