<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countrymodel extends Model
{
    use HasFactory;
    protected $table = 'country';
    protected $fillable = [
        'country'


    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
}
