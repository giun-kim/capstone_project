<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class path extends Model
{
    protected $table = 'path';
    protected $fillable = [
        'path_id','path_start_point', 'path_end_point'
    ];
}
