<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'station_name', 'station_lat', 'station_lon'
    ];

    protected $primaryKey = 'station_name';

    public $timestamps = false; // created_at, updated_at 취소하기

    protected $table = 'station';    
}
