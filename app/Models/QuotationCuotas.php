<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationCuotas extends Model
{
    protected $table='quotationcuotas';

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