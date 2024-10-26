<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotation';

    protected $fillable = [
        'codigo',
        'number_document',
        'client',
        'address_client',
        'date_issue',
        'delivery_time',
        'forma_pago',
        'subtotal',
        'igv',
        'total',
        'adelanto',
        'saldo',
        'pendiente_pago',
        'notes',
        'descripciontotal',
        'document',        
        'status',        
        'user_add',
        'enabled',
        'idstore',
        'idtype_document',
        'idcourier',
        'idvendor',
        'idtype_manufacturing',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function quotationdetail()
    {
        return $this->hasMany(QuotationDetail::class,'idquotation','id')->with('laboratory');
    }
    public function quotationcuotas()
    {
        return $this->hasMany(QuotationCuotas::class,'idquotation','id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class,'idstore','id')->select(['id','address','idheadquarter'])->with('headquarters');
    }
    public function courier()
    {
        return $this->belongsTo(Employee::class,'idcourier','id')->select(['id','name','lastname']);
    }
    public function vendor()
    {
        return $this->belongsTo(Employee::class,'idvendor','id')->select(['id','name','lastname']);
    }
    public function typedocument()
    {
        return $this->belongsTo(TypeDocument::class,'idtype_document','id')->select(['id','description']);
    }
    public function typemanufacturing()
    {
        return $this->belongsTo(TypeManufacturing::class,'idtype_manufacturing','id')->select(['id','description']);
    }
}
?>