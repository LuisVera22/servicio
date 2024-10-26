<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreHousexStore extends Model
{
    protected $table = 'store_storehouse';
    protected $primaryKey = ['idstorehouse', 'idstore'];
    public $incrementing = false;

    protected $fillable = [
        'idstorehouse',
        'idstore',
        'quantity',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];

    public function storehouse()
    {
        return $this->belongsTo(StoreHouse::class,'idstorehouse','id','idproduct')->with('products');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'idstore','id')->select(['id','address','idheadquarter'])->with('headquarters');
    }
}
?>