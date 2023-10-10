<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settingsmodel extends Model
{
  
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = [
        'background_image',
        'nav_bar_background',       
        'create_user',        
        'update_user',
       

    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
?>
