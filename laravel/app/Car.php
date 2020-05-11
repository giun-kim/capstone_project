<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'car';
    protected $fillable = [
        'car_num','car_name', 'car_status', 'car_lat', 'car_lon'
    ];
}
