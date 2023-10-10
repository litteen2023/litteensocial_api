<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class NewsfeedMutemodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'newsfeedmute';
    protected $fillable = [
        			
        'user_id',        
        'user_for_id',
               
        'status',     
        'create_user',        
        'update_user',

  

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
