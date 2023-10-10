<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interestsmodel extends Model
{
    use HasFactory;
    protected $table = 'interest';
    protected $fillable = [
        'interest'
        

    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
