<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreHouse extends Model
{
    protected $table = 'storehouse';

    protected $fillable = [
        'codigo',
        'product',
        'idcombination',
        'idproduct',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];

    public function storehousexstore()
    {        
        return $this->hasOne(StoreHousexStore::class,'idstorehouse','id')->with('store')->select(['idstorehouse','quantity', 'idstore']);
    }

    public function typemanufacturing()
    {
        return $this->belongsTo(TypeManufacturing::class,'idtype_manufacturing','id')->select(['id','description']);
    }
    
    public function products()
    {
        return $this->belongsTo(Products::class,'idproduct','id')->select(['id','description','idtype_manufacturing'])->with('typemanufacturing');
    }
}

?>