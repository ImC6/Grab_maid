<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = ['vendor_id', 'name', 'desc', 'company_logo', 'address_line', 'postcode', 'region', 'city', 'state'];

    public function vendor()
    {
        return $this->belongsTo(\App\User::class, 'vendor_id');
    }

    public function bank()
    {
        return $this->hasOne(\App\Models\Bank::class, 'company_id');
    }

    public function services()
    {
        return $this->belongsToMany(\App\Models\Service::class, 'vendor_service', 'company_id', 'service_id')
                    ->withPivot('id', 'guid', 'regions', 'city', 'state', 'start_date', 'end_date', 'start_time', 'duration', 'price', 'cleaners', 'working_day')
                    ->withTimestamps()
                    ->using(\App\Models\VendorService::class);
    }

    // public function vendorServices()
    // {
    //     return $this->hasMany(\App\Models\VendorService::class, 'company_id');
    // }

    public function getCompanyLogoAttribute($value = null)
    {
        if (is_null($value)) {
            return null;
        }

        return asset(Storage::url($value));
    }
}
