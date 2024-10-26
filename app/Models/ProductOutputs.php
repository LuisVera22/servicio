<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOutputs extends Model
{
    protected $table = 'productoutputs';

    protected $fillable = [
        'date',
        'idstore',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class,'idstore','id')->with('headquarters');
    }
}