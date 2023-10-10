<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertiserrmodel extends Model
{
    use HasFactory;

    protected $table = 'advertiser';
    protected $fillable = [
        'company_name',
        'company_address',
        'mobile',
        'email',
        'firstname',
        'billing_address',
        'password',
        'cc_type',
        'cc_number',
        'cc_exp_month',
        'cc_exp_year',
        'cc_name',
        'cc_cvc',
        'lastname',
        'create_user',
        'update_user',
        'sendcompanyaddress'

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function setPasswordAttribute($value)
    {
    $this->attributes['password'] = bcrypt($value);
    }
}
