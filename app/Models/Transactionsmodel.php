<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactionsmodel extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    protected $fillable = [
        'user_id', 
        'item_name',
        'item_price',               
        'paid_amount',    
        'item_price_currency',   
        'paid_amount_currency',  
        'txn_id',
        'payment_status',   
      
      

    ];
   
    
}
