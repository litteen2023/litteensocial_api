<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class StoriesLikemodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'stories_like_tbl';
    protected $fillable = [
        			
        'user_id',        
        'like_type',
        'stories_id',              
        'status',     
        'create_user',        
        'update_user',
  

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
