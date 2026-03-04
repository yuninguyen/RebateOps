<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    // Cho phép lưu các cột này
    protected $fillable = [
        'name',
        'platform',
        'slug',
        'boost_percentage',
        'maximum_limit',
    ];
}
