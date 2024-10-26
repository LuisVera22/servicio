<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'description',
        'category',
        'price',
        'enabled',
        'abrevtype',
        'abrevsubtype',
        'abrevmaterials',
        'abrevclasses',
        'abrevsubclasses',
        'idtype_manufacturing',
        'created_at',
        'updated_at'
    ];

    public function productsxstore()
    {
        return $this->hasOne(ProductsxStore::class,'idproduct','id')->with('store')->select(['idproduct', 'idstore']);
    }

    public function typemanufacturing()
    {
        return $this->belongsTo(TypeManufacturing::class,'idtype_manufacturing','id')->select(['id','description','abbreviation']);
    }
}
?>