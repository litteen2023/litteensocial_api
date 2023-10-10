<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storiestimedurationmodel extends Model
{
    use HasFactory;

    protected $table = 'stories';
    protected $fillable = [
        'time_duration',
        
        'status',       
        'create_user',        
        'update_user'

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
}
