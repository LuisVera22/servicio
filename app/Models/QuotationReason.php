<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationReason extends Model
{
    protected $table = 'quotationreason';

    protected $fillable = [
        'typereason',
        'reason',
        'datetime',
        'idquotation',
        'created_at',
        'updated_at'
    ];
}
?>