<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suppiler extends Model
{
    protected $table = 'suppiler';

    protected $fillable = [
        'number_document',
        'bussinesname',
        'address',
        'email',
        'cell_phone',
        'contact',
        'enabled',
        'created_at',
        'updated_at'
        
    ];
}
?>