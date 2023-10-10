<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Usermodel extends Model
{
    use HasFactory;
    protected $table = 'user_managements';
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
