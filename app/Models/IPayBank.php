<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IPayBank extends Model
{
    protected $table = 'ipay_banks';

    protected $fillable = [
        'payment_id',
        'name',
        'icon'
    ];

    public function getIconAttribute($icon)
    {
        return url('/') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $icon;
    }
}
