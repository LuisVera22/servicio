<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOutputsDetail extends Model
{
    protected $table = 'productoutputsdetail';

    protected $fillable = [
        'quantityouput',
        'status',
        'idproductouput',
        'idstorehouse',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];

    public function productoutputs()
    {
        return $this->belongsTo(ProductOutputs::class,'idproductouput','id');
    }

    public function storehouse()
    {
        return $this->belongsTo(StoreHouse::class,'idstorehouse','id');
    }
}