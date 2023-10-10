<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class StoriesCommentmodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'stories_comment';
    protected $fillable = [
        			
        'user_id',        
        'stories_id',        
        'description',       
        'parent_id',         
        'status',     
        'create_user',        
        'update_user',

  

    ];
   
}
