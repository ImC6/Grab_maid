<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $hidden = [
        'id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function activities()
    {
        return $this->hasMany(\App\Models\WalletActivity::class, 'wallet_id');
    }
}
