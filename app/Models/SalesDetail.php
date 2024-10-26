<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    protected $table = 'salesdetail';

    protected $fillable = [
        'description',
        'quantity',
        'price',
        'pricesinigv',
        'subtotal',
        'igv',
        'total',
        'idsales'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
?>