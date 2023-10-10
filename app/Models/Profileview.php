<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Profileview extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'profile_view';
    protected $fillable = [
        			
        'user_id',        
        'profile_id',
        'like_type',              
        'status',     
        'create_user',        
        'update_user',
  

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
