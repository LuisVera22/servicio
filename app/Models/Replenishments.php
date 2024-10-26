<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replenishments extends Model
{
    protected $table = 'replenishments';

    protected $fillable = [
        'description',        
        'codigoquotation',
        'replacementmount',
        'oldquantity',
        'newquantity',
        'date',
        'datetime',        
        'enabled',
        'idquotation',
        'idstorehouse',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];

    public function storehousexstore()
    {
        return $this->belongsTo(StoreHousexStore::class,'idstorehouse','idstorehouse','idstorehouse','idstore')->with('storehouse','store');
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class,'idquotation','id','idstore','idcourier','idvendor','idtype_document','idtype_manufacturing')->with(['store','courier','vendor','typedocument','typemanufacturing']);
    }
}