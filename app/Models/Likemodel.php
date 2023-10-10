<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Likemodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'like_tbl';
    protected $fillable = [
        			
        'user_id',        
        'like_type',
        'news_feed_id', 
        'create_user',        
        'update_user',
  

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
