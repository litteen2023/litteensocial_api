<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Albummodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'album_tbl';
    protected $fillable = [
        			
        'user_id',        
        'album_type',
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
