<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class path_check extends Model
{
    protected $table = 'path_check';
    protected $fillable = [
        'path_check_id','path_col_id', 'check_id', 'sequence'
    ];
}
