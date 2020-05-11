<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class station extends Model
{
    protected $table = 'station';
    protected $fillable = [
        'station_id','station_name', 'station_lat', 'station_lon'
    ];
}
