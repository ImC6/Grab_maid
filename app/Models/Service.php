<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = ['name', 'image', 'details', 'slug'];

    public function getImageAttribute($value = null)
    {
        if (is_null($value)) {
            return null;
        }

        return asset(Storage::url($value));
    }
}
