<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisment extends Model
{
    use HasFactory;

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function advertiser()
    {
        return $this->belongsTo('App\Models\Advertiserrmodel');
    }
}
