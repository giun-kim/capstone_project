<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class checkpoint extends Model
{
    protected $table = 'checkpoint';
    protected $fillable = [
        'checkpoint_id','checkpoint_lat', 'checkpoint_lon'
    ];
}
