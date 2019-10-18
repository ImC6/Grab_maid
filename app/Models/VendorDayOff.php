<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDayOff extends Model
{
    protected $table = 'vendor_day_off';

    protected $fillable = ['vendor_id', 'date', 'desc', 'created_by'];

    public function vendor()
    {
        return $this->belongsTo(\App\User::class, 'vendor_id');
    }
}
