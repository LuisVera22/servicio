<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCuotas extends Model
{
    protected $table = 'salescuotas';

    protected $fillable=[
        'monto',
        'fecha',
        'idquotation',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
?>