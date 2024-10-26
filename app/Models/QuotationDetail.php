<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationDetail extends Model
{
    protected $table = 'quotationdetail';

    protected $fillable = [
        'description',
        'manufacturing',
        'detail_manufacturing',
        'unidadmedida',
        'quantity',
        'price',
        'discount',
        'pricesinigv',
        'subtotal',
        'igv',
        'total',
        'material',
        'modelo',
        'condicion',
        'color',
        'marca',
        'idquotation',
        'idproduct',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function laboratory()
    {
        return $this->hasOne(Laboratory::class, 'idquotationdetail', 'id');
    }
}
?>