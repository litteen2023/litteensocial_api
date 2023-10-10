<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Usermodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
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
        'username',
		'password',
        'birthday',
        'remember_token',
        'fcm_token',
        'registration_type',
        'user_id',
        'is_first_login',
        'stripe_customer_id',
        'agora_id',
        'qb_id',
        'qb_password',

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];

    // Likeprofilemodel
    public function getlikeprofile()
    {
        return $this->hasOne('App\Models\Likeprofilemodel', 'user_id', 'id');
    }

    public function getFullNameIdAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function countryDetail()
    {
        return $this->belongsTo(Countrymodel::class, 'country', 'country_id');
    }

    public function friend_list()
    {
        return $this->hasMany('App\Models\Friendrequestmodel', 'friend_id', 'id');
    }

    public function friends()
    {
        return $this->belongsToMany(Usermodel::class, 'friend_tbl', 'friend_id', 'friend_for_id')->where('friend_tbl.friend_request_type', 2);
    }
}
