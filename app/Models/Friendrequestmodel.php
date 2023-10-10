<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Friendrequestmodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'friend_tbl';
    protected $fillable = [

        'friend_request_type',
        'friend_id',
        'friend_for_id',
        'status',
        'create_user',
        'update_user',


    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];


    public function users()
    {
        return $this->belongsTo('App\Models\Usermodel', 'friend_id');
    }

    public function friends_zone()
    {
        return $this->belongsTo('App\Models\Usermodel', 'friend_for_id');
    }
}
