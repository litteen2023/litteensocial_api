<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscribemodel extends Model
{
    use HasFactory;
    protected $table = 'subscribe';
    protected $fillable = [
        'description',
        'days',
        'subscribe_price',        
        'subscribe_title',
        'status',     
        'create_user',        
        'update_user',
      

    ];
   
    
}
