<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertiserrmodel extends Model
{
    use HasFactory;

    protected $table = 'advertiser';
    protected $fillable = [
        'background_image',
        'profile_picture',
        'mobile',        
        'email',
        'firstname',
        'premium',
        'bgc_verified',
        'lastname',
        'address',        
        'pincode',
        'city',
        'country',        
        'status',     
        'create_user',        
        'update_user',
        'username'

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
}
