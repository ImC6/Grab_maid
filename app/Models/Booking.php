<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $fillable = [
        'booking_number',
        'user_id',
        'address_id',
        'vendor_service_id',
        'booking_date',
        'promotion_id',
        'price',
        'insurance',
        'service_tax',
        'total_price',
        'payment_type',
        'remarks',
        'receipt',
        'refunded',
        'status',
        'created_by'
    ];
    protected $hidden = [
        'user_id',
        'created_by'
    ];

    /* Relation Start */
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function vendorService()
    {
        return $this->belongsTo(\App\Models\VendorService::class, 'vendor_service_id');
    }

    public function promotion()
    {
        return $this->belongsTo(\App\Models\Promotion::class, 'promotion_id');
    }

    public function address()
    {
        return $this->belongsTo(\App\Models\Address::class, 'address_id')->withTrashed();
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class, 'booking_id');
    }
    /* Relation End */

    /* Attribute Helper Start */
    public function isCancelled()
    {
        return $this->getAttribute('status') === config('grabmaid.booking.status.cancelled');
    }

    public function isAccepted()
    {
        return $this->getAttribute('status') === config('grabmaid.booking.status.accepted');
    }

    public function isUnpaid()
    {
        return $this->isAccepted();
    }

    public function isPaid()
    {
        return $this->getAttribute('status') === config('grabmaid.booking.status.paid');
    }

    public function isDelivering()
    {
        return $this->getAttribute('status') === config('grabmaid.booking.status.delivering');
    }

    public function isInProgress()
    {
        return $this->getAttribute('status') === config('grabmaid.booking.status.inProgress');
    }

    public function isDone()
    {
        return $this->getAttribute('status') === config('grabmaid.booking.status.done');
    }

    public function isRated()
    {
        return $this->getAttribute('rated') === config('grabmaid.booking.status.rated');
    }

    public function isStarted()
    {
        return $this->getAttribute('status') > config('grabmaid.booking.status.paid');
    }

    public function isAfterPaid()
    {
        return $this->getAttribute('status') >= config('grabmaid.booking.status.paid');
    }
    /* Attribute Helper End */

    /**
     * Scope a query to filter user with role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', config('grabmaid.booking.status.accepted'));
    }

    /**
     * Scope a query to filter user with role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', config('grabmaid.booking.status.paid'));
    }

    /**
     * Scope a query to filter user with role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAfterPaid($query)
    {
        return $query->where('status', '>=', config('grabmaid.booking.status.paid'));
    }
}
