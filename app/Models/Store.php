<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'store';

    protected $fillable =[
        'address',
        'phone',
        'phone_2',
        'phone_3',
        'main',
        'enabled',
        'idbusiness',
        'idheadquarter',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'idheadquarter',
        'created_at',
        'updated_at'
    ];
    
    public function headquarters()
    {
        return $this->belongsTo(Headquarters::class, 'idheadquarter', 'id')->select(['id', 'description']);
    }
}
