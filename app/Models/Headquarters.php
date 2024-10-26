<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Headquarters extends Model
{
    protected $table = 'headquarters';

    protected $fillable = [
        'id',
        'description',
        'enabled',
        'created_at',
        'updated_at'
    ];
}
