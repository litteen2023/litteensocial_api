<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storiesmodel extends Model
{
    use HasFactory;

    protected $table = 'stories';
    protected $fillable = [
        'user_id',
        'stories_type',
        'file',
        'file_type',
        'description',
        'visible_type',
        'parent_id',
        'tag',        
        'status',       
        'create_user',        
        'update_user'

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
}
