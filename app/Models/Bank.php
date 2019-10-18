<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';

    protected $fillable = ['company_id', 'bank_name', 'bank_account', 'bank_account_name'];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
