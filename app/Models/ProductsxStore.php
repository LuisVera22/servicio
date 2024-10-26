<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsxStore extends Model
{
    protected $table = 'products_store';

    protected $fillable = [
        'idproduct',
        'idstore',        
        'created_at',
        'updated_at'
    ];

    public function products()
    {
        return $this->belongsTo(Products::class,'idproduct','id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'idstore','id')->select(['id','address','idheadquarter'])->with('headquarters');
    }
}
?>