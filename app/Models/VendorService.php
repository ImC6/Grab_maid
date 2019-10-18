<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class VendorService extends Pivot
{
    protected $table = 'vendor_service';

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class, 'service_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function review()
    {
        return $this->hasOne(\App\Models\Review::class, 'vendor_service_id');
    }

    public function getStartDateAttribute($date = null)
    {
        if (is_null($date)) {
            return null;
        }
        return Carbon::parse($date);
    }

    public function getEndDateAttribute($date = null)
    {
        if (is_null($date)) {
            return null;
        }
        return Carbon::parse($date);
    }
}

// class VendorService extends Model
// {
//     protected $table = 'vendor_service';
//     protected $fillable = ['guid', 'regions', 'city', 'state', 'start_date', 'end_date', 'start_time', 'duration', 'price', 'cleaners', 'working_day'];

//     public function service()
//     {
//         return $this->belongsTo(\App\Models\Service::class, 'service_id');
//     }

//     public function company()
//     {
//         return $this->belongsTo(\App\Models\Company::class, 'company_id');
//     }

//     public function review()
//     {
//         return $this->hasOne(\App\Models\Review::class, 'vendor_service_id');
//     }

//     public function getStartDateAttribute($date = null)
//     {
//         if (is_null($date)) {
//             return null;
//         }
//         return Carbon::parse($date);
//     }

//     public function getEndDateAttribute($date = null)
//     {
//         if (is_null($date)) {
//             return null;
//         }
//         return Carbon::parse($date);
//     }
// }
