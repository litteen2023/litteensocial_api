<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usersubscribemodel extends Model
{
    use HasFactory;
    protected $table = 'user_subscribe';
    protected $fillable = [
        'user_id',
        'subscribe_id',
		'subscribe_title',
        'description',
        'subscribe_price',
        'exp_date',
		'transaction_id',
        'status',     
        'create_user',        
        'update_user',
      
        
       

    ];
   
}
