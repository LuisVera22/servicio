<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'transactiontype',
        'quantity',
        'quantitystorehouse',
        'newquantiry',
        'transactiondate',
        'idstorehouse',
        'created_at ',
        'updated_at'
    ];

    protected $hidden = [        
        'created_at',
        'updated_at'
    ];
}
?>