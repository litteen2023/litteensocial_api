<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Newsfeedmodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'news_feed_tbl';
    protected $fillable = [
        			
        'user_id',        
        'news_type',
        'file',  
        'description',
		'new_description',
        'visible_type',
        'parent_id',
        'tag',
		'thumbnail',
        'file_type',		
        'remember_token',         
        'status',     
        'create_user',        
        'update_user',

  

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
