<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'guid',
        'name',
        'email',
        'email_verified_at',
        'verification_code',
        'password',
        'status',
        'gender',
        'mobile_no',
        'fb_id',
        'google_id',
        'role',
        'cleaner_of',
        'profile_pic'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'cleaner_of'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function verificationCode()
    {
        return $this->hasOne(\App\Models\VerificationCode::class, 'user_id');
    }

    /* Vendor start */

    // public function services()
    // {
    //     return $this->belongsToMany(\App\Models\Service::class, 'vendor_service', 'vendor_id', 'service_id')
    //                 ->withPivot('region', 'start_time', 'duration', 'start_date', 'end_date', 'cleaners', 'price')
    //                 ->withTimestamps()
    //                 ->using(\App\Models\VendorService::class);
    // }

    public function cleaners()
    {
        return $this->hasMany(self::class, 'cleaner_of');
    }

    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class, 'vendor_id');
    }

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'user_id');
    }

    public function dayOff()
    {
        return $this->hasMany(\App\Models\VendorDayOff::class, 'vendor_id');
    }

    /* Vendor end */

    /* Cleaner start */

    public function vendor()
    {
        return $this->belongsTo(self::class, 'cleaner_of');
    }

    /* Cleaner end */

    /* Customer start */

    public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(\App\Models\Wallet::class, 'user_id');
    }

    public function communicationSetting()
    {
        return $this->hasOne(\App\Models\CommunicationSetting::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class, 'user_guid', 'guid');
    }

    /* Customer end */

    public function token()
    {
        return $this->hasMany(Models\UserToken::class, 'user_id');
    }

    /* Accessor Start */

    public function getGenderAttribute($value = null)
    {
        if ($value === 1) {
            return 'male';
        }

        if ($value === 2) {
            return 'female';
        }

        return null;
    }

    public function getProfilePicAttribute($value = null)
    {
        if (is_null($value)) {
            return null;
        }

        return asset(Storage::url($value));
    }

    /* Accessor End */

    /* Attribute Reference Start */

    public function hasRole(int $role)
    {
        return $this->getAttribute('role') === $role;
    }

    /* Attribute Reference End */

    /**
     * Scope a query to filter user with role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCleanerOf($query, int $vendorId)
    {
        return $query->where('cleaner_of', $vendorId);
    }

    /**
     * Scope a query to filter user with role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, int $role = 0)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to filter vendor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query)
    {
        return $query->role(1);
    }

    /**
     * Scope a query to filter vendor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVendor($query)
    {
        return $query->role(2);
    }

    /**
     * Scope a query to filter vendor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCleaner($query)
    {
        return $query->role(3);
    }

    /**
     * Scope a query to filter vendor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustomer($query)
    {
        return $query->role(4);
    }


}
